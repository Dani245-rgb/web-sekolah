<?php

namespace App\Services;

use App\Models\ImportLogModel;
use App\Models\JurusanModel;
use App\Models\SiswaModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

/**
 * Import siswa dari Excel, diproses per batch (dipanggil berulang oleh JS).
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class ImportSiswaService
{
    private const BATCH_SIZE = 500;
    private const MAKS_BYTES = 10 * 1024 * 1024; // 10MB
    private const MIME_EXCEL = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
        'application/vnd.ms-excel',                                          // xls
    ];

    /** Kolom Excel untuk tiap field (kolom E = tanggal lahir, ditangani terpisah) */
    private const KOLOM = [
        'nis'            => 'A',
        'nisn'           => 'B',
        'nama'           => 'C',
        'tempat_lahir'   => 'D',
        'jenis_kelamin'  => 'F',
        'agama'          => 'G',
        'alamat'         => 'H',
        'nama_ayah'      => 'I',
        'nama_ibu'       => 'J',
        'pekerjaan_ortu' => 'K',
        'no_hp_ortu'     => 'L',
        'email'          => 'M',
        'jurusan'        => 'N',
    ];

    /** kode/singkatan/nama jurusan (huruf kecil) => id_jurusan */
    private array $jurusanMap = [];

    public function __construct(
        private ImportLogModel $logModel,
        private SiswaModel $siswaModel,
        private JurusanModel $jurusanModel,
        private SiswaService $siswaService,
    ) {
    }

    public function jobBelumSelesai()
    {
        return $this->logModel->getJobBelumSelesai();
    }

    /**
     * Isi file template .xlsx (kosong + 1 baris contoh).
     */
    public function buatTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'NIS', 'NISN', 'Nama', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)',
            'Jenis Kelamin (L/P)', 'Agama', 'Alamat', 'Nama Ayah', 'Nama Ibu',
            'Pekerjaan Ortu', 'No HP Ortu', 'Email', 'Jurusan (Kode/Singkatan/Nama)',
        ], null, 'A1');

        $sheet->fromArray([
            '2024001', '0012345678', 'Contoh Nama Siswa', 'Jakarta', '2009-05-17',
            'L', 'Islam', 'Jl. Contoh No. 1', 'Nama Ayah', 'Nama Ibu',
            'Wiraswasta', '081234567890', 'contoh@email.com', 'TKJ',
        ], null, 'A2');

        $tmp = tempnam(sys_get_temp_dir(), 'tpl');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmp);
        $isi = (string) file_get_contents($tmp);
        unlink($tmp);

        return $isi;
    }

    /**
     * Simpan file upload, hitung total baris, buat job baru.
     *
     * @return array{id_import_log: int|string, total_baris: int}
     */
    public function terimaUpload(?UploadedFile $file): array
    {
        if ($file === null || !$file->isValid()) {
            throw new InvalidArgumentException('File tidak valid atau gagal diupload.');
        }

        // getMimeType() membaca isi file asli, bukan nama file kiriman browser (gampang dipalsukan)
        if (!in_array($file->getMimeType(), self::MIME_EXCEL, true)) {
            throw new InvalidArgumentException('File harus format .xlsx atau .xls yang valid.');
        }

        if ($file->getSize() > self::MAKS_BYTES) {
            throw new InvalidArgumentException('Ukuran file maksimal 10MB.');
        }

        $namaAsli   = $file->getClientName();
        $namaSimpan = $file->getRandomName();
        $folder     = WRITEPATH . 'uploads/import';
        $file->move($folder, $namaSimpan);
        $pathFile = $folder . '/' . $namaSimpan;

        try {
            $sheet      = IOFactory::load($pathFile)->getActiveSheet();
            $totalBaris = max(0, $sheet->getHighestDataRow() - 1); // dikurangi baris header
        } catch (Throwable $e) {
            @unlink($pathFile);
            throw new InvalidArgumentException('File Excel tidak bisa dibaca. Pastikan file tidak rusak.');
        }

        if ($totalBaris <= 0) {
            unlink($pathFile);
            throw new InvalidArgumentException('File kosong atau tidak ada data setelah baris header.');
        }

        $idImportLog = $this->logModel->insert([
            'nama_file'     => $namaAsli,
            'path_file'     => $pathFile,
            'total_baris'   => $totalBaris,
            'baris_selesai' => 0,
            'status'        => 'Proses',
        ]);

        return ['id_import_log' => $idImportLog, 'total_baris' => $totalBaris];
    }

    public function statusJob(int $idImportLog): ?array
    {
        $job = $this->logModel->find($idImportLog);
        if (!$job) {
            return null;
        }

        return [
            'baris_selesai' => $job['baris_selesai'],
            'total_baris'   => $job['total_baris'],
            'total_sukses'  => $job['total_sukses'],
            'total_gagal'   => $job['total_gagal'],
            'status'        => $job['status'],
            'detail_gagal'  => json_decode($job['detail_gagal'] ?? '[]', true),
        ];
    }

    /**
     * Proses satu batch. Kalau terjadi error tak terduga, status job dikembalikan
     * ke 'Proses' (supaya bisa dilanjutkan) lalu exception dilempar ulang.
     */
    public function prosesBatch(int $idImportLog): array
    {
        $job = $this->logModel->find($idImportLog);
        if (!$job) {
            throw new InvalidArgumentException('Job import tidak ditemukan.');
        }

        if ($job['status'] === 'Selesai') {
            return [
                'selesai'       => true,
                'baris_selesai' => $job['baris_selesai'],
                'total_baris'   => $job['total_baris'],
                'total_sukses'  => $job['total_sukses'],
                'total_gagal'   => $job['total_gagal'],
            ];
        }

        // Status Diproses_Batch = ada request lain yang sedang memproses
        // klaimBatch() = update atomik WHERE status='Proses', cegah race condition
        if ($job['status'] === 'Diproses_Batch' || !$this->logModel->klaimBatch($idImportLog)) {
            return [
                'sedang_diproses' => true,
                'baris_selesai'   => $job['baris_selesai'],
                'total_baris'     => $job['total_baris'],
            ];
        }

        try {
            return $this->kerjakanBatch($job);
        } catch (Throwable $e) {
            $this->logModel->update($idImportLog, ['status' => 'Proses']);

            throw $e;
        }
    }

    private function kerjakanBatch(array $job): array
    {
        $sheet = IOFactory::load($job['path_file'])->getActiveSheet();

        $barisMulai        = (int) $job['baris_selesai'] + 2; // +1 header, +1 karena baris Excel mulai dari 1
        $barisSelesaiBatch = min($barisMulai + self::BATCH_SIZE - 1, $sheet->getHighestDataRow());

        $this->muatJurusanMap();

        $sukses = 0;
        $gagal  = 0;
        $error  = [];

        for ($row = $barisMulai; $row <= $barisSelesaiBatch; $row++) {
            $data = $this->bacaBaris($sheet, $row);

            // Baris kosong total: lewati diam-diam, tidak dihitung sukses/gagal
            if (empty($data['nis']) && empty($data['nama'])) {
                continue;
            }

            $hasil = $this->prosesSatuBaris($data, $row - 1); // nomor baris data (tanpa header)

            if ($hasil['sukses']) {
                $sukses++;
            } else {
                $gagal++;
                $error[] = $hasil['pesan'];
            }
        }

        $barisSelesaiBaru = $barisSelesaiBatch - 1; // balik ke jumlah baris data yang sudah diproses
        $detailGagal      = array_merge(json_decode($job['detail_gagal'] ?? '[]', true) ?: [], $error);
        $status           = $barisSelesaiBaru >= $job['total_baris'] ? 'Selesai' : 'Proses';
        $totalSukses      = $job['total_sukses'] + $sukses;
        $totalGagal       = $job['total_gagal'] + $gagal;

        $this->logModel->update($job['id_import_log'], [
            'baris_selesai' => $barisSelesaiBaru,
            'total_sukses'  => $totalSukses,
            'total_gagal'   => $totalGagal,
            'detail_gagal'  => json_encode($detailGagal),
            'status'        => $status,
        ]);

        return [
            'selesai'       => $status === 'Selesai',
            'baris_selesai' => $barisSelesaiBaru,
            'total_baris'   => $job['total_baris'],
            'total_sukses'  => $totalSukses,
            'total_gagal'   => $totalGagal,
        ];
    }

    private function bacaBaris(Worksheet $sheet, int $row): array
    {
        $data = [];
        foreach (self::KOLOM as $field => $kolom) {
            $data[$field] = trim((string) $sheet->getCell($kolom . $row)->getValue());
        }

        $data['jenis_kelamin'] = strtoupper($data['jenis_kelamin']);
        $data['tanggal_lahir'] = $this->normalisasiTanggal($sheet->getCell("E{$row}"));

        return $data;
    }

    /**
     * Validasi dan simpan satu baris. Gagal di sini tidak mempengaruhi baris lain.
     * $data['tanggal_lahir'] sudah dinormalisasi (Y-m-d) atau null kalau tidak dikenali.
     *
     * @return array{sukses: bool, pesan: string}
     */
    public function prosesSatuBaris(array $data, int $nomorBaris): array
    {
        if (empty($data['nis']) || empty($data['nisn']) || empty($data['nama'])) {
            return $this->gagalBaris($nomorBaris, 'NIS/NISN/Nama kosong, dilewati.');
        }

        if (!in_array($data['jenis_kelamin'], ['L', 'P'], true)) {
            return $this->gagalBaris($nomorBaris, "Jenis Kelamin '{$data['jenis_kelamin']}' tidak valid (harus L/P).");
        }

        // Rule model tidak jalan (skipValidation), jadi email dicek manual di sini
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->gagalBaris($nomorBaris, "Format email '{$data['email']}' tidak valid.");
        }

        // Jurusan kosong = boleh, tidak cocok = ditolak
        $jurusanId = null;
        if (!empty($data['jurusan'])) {
            $kunci = mb_strtolower($data['jurusan']);
            if (!isset($this->jurusanMap[$kunci])) {
                return $this->gagalBaris($nomorBaris, "Jurusan '{$data['jurusan']}' tidak ditemukan di data Jurusan.");
            }
            $jurusanId = $this->jurusanMap[$kunci];
        }

        if ($this->siswaModel->nisSudahAda($data['nis'])) {
            return $this->gagalBaris($nomorBaris, "NIS '{$data['nis']}' sudah terdaftar, dilewati.");
        }
        if ($this->siswaModel->nisnSudahAda($data['nisn'])) {
            return $this->gagalBaris($nomorBaris, "NISN '{$data['nisn']}' sudah terdaftar, dilewati.");
        }

        if (empty($data['tanggal_lahir'])) {
            return $this->gagalBaris($nomorBaris, 'Format Tanggal Lahir tidak dikenali.');
        }

        $hasil = $this->siswaService->buatSiswaBaru([
            'nis'            => $data['nis'],
            'nisn'           => $data['nisn'],
            'jurusan_id'     => $jurusanId,
            'nama'           => $data['nama'],
            'tempat_lahir'   => $data['tempat_lahir'],
            'tanggal_lahir'  => $data['tanggal_lahir'],
            'jenis_kelamin'  => $data['jenis_kelamin'],
            'agama'          => $data['agama'],
            'alamat'         => $data['alamat'],
            'nama_ayah'      => $data['nama_ayah'],
            'nama_ibu'       => $data['nama_ibu'],
            'pekerjaan_ortu' => $data['pekerjaan_ortu'],
            'no_hp_ortu'     => $data['no_hp_ortu'],
            'email'          => $data['email'] ?: null,
        ]);

        if (!$hasil['sukses']) {
            return $this->gagalBaris($nomorBaris, $hasil['pesan']);
        }

        return ['sukses' => true, 'pesan' => ''];
    }

    /**
     * Peta kode/singkatan/nama jurusan (huruf kecil) => id_jurusan, dimuat sekali per batch.
     */
    public function muatJurusanMap(): void
    {
        $this->jurusanMap = [];

        foreach ($this->jurusanModel->getUntukPencocokan() as $j) {
            foreach ([$j['kode_jurusan'] ?? null, $j['singkatan'] ?? null, $j['nama_jurusan'] ?? null] as $kunci) {
                if (!empty($kunci)) {
                    $this->jurusanMap[mb_strtolower(trim($kunci))] = (int) $j['id_jurusan'];
                }
            }
        }
    }

    /**
     * Deteksi 3 kemungkinan format tanggal (serial Excel, YYYY-MM-DD, DD/MM/YYYY) => Y-m-d.
     */
    private function normalisasiTanggal(Cell $cell): ?string
    {
        if ($cell->getDataType() === DataType::TYPE_NUMERIC) {
            try {
                return ExcelDate::excelToDateTimeObject($cell->getValue())->format('Y-m-d');
            } catch (Throwable $e) {
                return null;
            }
        }

        $value = trim((string) $cell->getValue());
        if (empty($value)) {
            return null;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $value);
        if ($d && $d->format('Y-m-d') === $value) {
            return $value;
        }

        $d = \DateTime::createFromFormat('d/m/Y', $value);
        if ($d) {
            return $d->format('Y-m-d');
        }

        return null;
    }

    private function gagalBaris(int $nomorBaris, string $pesan): array
    {
        return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: {$pesan}"];
    }
}