<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ImportLogModel;
use App\Models\UserModel;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;
use Config\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ImportSiswa extends BaseController
{
    protected ImportLogModel $importLogModel;
    protected UserModel $userModel;
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected TahunAjaranModel $tahunAjaranModel;

    protected int $batchSize = 500;

    public function __construct()
    {
        $this->importLogModel   = new ImportLogModel();
        $this->userModel        = new UserModel();
        $this->siswaModel       = new SiswaModel();
        $this->kelasModel       = new KelasModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
    }

    // Ambil token CSRF terbaru untuk dikirim balik ke JS
    protected function csrfData(): array
    {
        return [
            'csrf_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
        ];
    }

    // Halaman upload + deteksi job belum selesai
    public function index()
    {
        $data['jobBelumSelesai'] = $this->importLogModel->getJobBelumSelesai();
        return view('admin/siswa/import', $data);
    }

    // Download template kosong
    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $header = [
            'NIS',
            'NISN',
            'Nama',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Jenis Kelamin (L/P)',
            'Agama',
            'Alamat',
            'Nama Ayah',
            'Nama Ibu',
            'Pekerjaan Ortu',
            'No HP Ortu',
            'Email',
            'Kelas',
        ];
        $sheet->fromArray($header, null, 'A1');

        // Contoh 1 baris biar admin ada gambaran
        $sheet->fromArray([
            '2024001',
            '0012345678',
            'Contoh Nama Siswa',
            'Jakarta',
            '2009-05-17',
            'L',
            'Islam',
            'Jl. Contoh No. 1',
            'Nama Ayah',
            'Nama Ibu',
            'Wiraswasta',
            '081234567890',
            'contoh@email.com',
            'X TKJ 1',
        ], null, 'A2');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        $filename = 'template_import_siswa.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // Terima file upload, hitung total baris, buat job baru
    public function upload()
    {
        $file = $this->request->getFile('file_excel');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['error' => 'File tidak valid atau gagal diupload.']);
        }

        // Pakai getMimeType() (deteksi dari isi file asli), bukan getClientExtension()
        // yang cuma baca nama file kiriman browser (gampang dipalsukan)
        $mimeValid = in_array($file->getMimeType(), [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
            'application/vnd.ms-excel', // xls
        ]);

        if (!$mimeValid) {
            return $this->response->setJSON(['error' => 'File harus format .xlsx atau .xls yang valid.']);
        }

        if ($file->getSize() > 10 * 1024 * 1024) { // 10MB
            return $this->response->setJSON(['error' => 'Ukuran file maksimal 10MB.']);
        }

        $tahunAktif = $this->tahunAjaranModel->getActive();
        if (!$tahunAktif) {
            return $this->response->setJSON(['error' => 'Tidak ada Tahun Ajaran Aktif. Aktifkan dulu sebelum import.']);
        }

        $namaFileAsli = $file->getClientName();
        $namaFileSimpan = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/import', $namaFileSimpan);
        $pathFile = WRITEPATH . 'uploads/import/' . $namaFileSimpan;

        // Hitung total baris data (tanpa proses isinya dulu)
        $spreadsheet = IOFactory::load($pathFile);
        $sheet = $spreadsheet->getActiveSheet();
        $totalBarisSheet = $sheet->getHighestDataRow(); // termasuk header
        $totalBaris = max(0, $totalBarisSheet - 1); // dikurangi baris header

        if ($totalBaris <= 0) {
            unlink($pathFile);
            return $this->response->setJSON(['error' => 'File kosong atau tidak ada data setelah baris header.']);
        }

        $idImportLog = $this->importLogModel->insert([
            'nama_file'     => $namaFileAsli,
            'path_file'     => $pathFile,
            'total_baris'   => $totalBaris,
            'baris_selesai' => 0,
            'status'        => 'Proses',
        ]);

        return $this->response->setJSON(array_merge([
            'id_import_log' => $idImportLog,
            'total_baris'   => $totalBaris,
        ], $this->csrfData()));
    }

    // Proses 1 batch (dipanggil berulang oleh JS)
    public function proses($id_import_log)
    {
        // Batas resource khusus untuk proses import (gak ganggu setting PHP global)
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $job = $this->importLogModel->find($id_import_log);
        if (!$job) {
            return $this->response->setJSON(['error' => 'Job import tidak ditemukan.']);
        }

        if ($job['status'] === 'Selesai') {
            return $this->response->setJSON(array_merge([
                'selesai'       => true,
                'baris_selesai' => $job['baris_selesai'],
                'total_baris'   => $job['total_baris'],
                'total_sukses'  => $job['total_sukses'],
                'total_gagal'   => $job['total_gagal'],
            ], $this->csrfData()));
        }

        if ($job['status'] === 'Diproses_Batch') {
            return $this->response->setJSON(array_merge([
                'sedang_diproses' => true,
                'baris_selesai'   => $job['baris_selesai'],
                'total_baris'     => $job['total_baris'],
            ], $this->csrfData()));
        }

        $this->importLogModel->update($id_import_log, ['status' => 'Diproses_Batch']);

        $offset = (int) $job['baris_selesai']; // 0-based, dihitung dari baris data (bukan termasuk header)
        $tahunAktif = $this->tahunAjaranModel->getActive();

        $spreadsheet = IOFactory::load($job['path_file']);
        $sheet = $spreadsheet->getActiveSheet();

        $barisMulai = $offset + 2; // +1 karena header, +1 karena Excel row dimulai dari 1
        $barisSelesaiBatch = min($barisMulai + $this->batchSize - 1, $sheet->getHighestDataRow());

        $suksesBatch = 0;
        $gagalBatch = 0;
        $errorBatch = [];

        for ($row = $barisMulai; $row <= $barisSelesaiBatch; $row++) {
            $nomorBarisData = $row - 1; // nomor baris data (header = 1, data pertama = baris 2 Excel = data ke-1)

            $rowData = [
                'nis'            => trim((string) $sheet->getCell("A{$row}")->getValue()),
                'nisn'           => trim((string) $sheet->getCell("B{$row}")->getValue()),
                'nama'           => trim((string) $sheet->getCell("C{$row}")->getValue()),
                'tempat_lahir'   => trim((string) $sheet->getCell("D{$row}")->getValue()),
                'tanggal_lahir'  => $sheet->getCell("E{$row}"),
                'jenis_kelamin'  => strtoupper(trim((string) $sheet->getCell("F{$row}")->getValue())),
                'agama'          => trim((string) $sheet->getCell("G{$row}")->getValue()),
                'alamat'         => trim((string) $sheet->getCell("H{$row}")->getValue()),
                'nama_ayah'      => trim((string) $sheet->getCell("I{$row}")->getValue()),
                'nama_ibu'       => trim((string) $sheet->getCell("J{$row}")->getValue()),
                'pekerjaan_ortu' => trim((string) $sheet->getCell("K{$row}")->getValue()),
                'no_hp_ortu'     => trim((string) $sheet->getCell("L{$row}")->getValue()),
                'email'          => trim((string) $sheet->getCell("M{$row}")->getValue()),
                'kelas'          => trim((string) $sheet->getCell("N{$row}")->getValue()),
            ];

            // Baris kosong total → lewati diam-diam, tidak dihitung sukses/gagal
            if (empty($rowData['nis']) && empty($rowData['nama'])) {
                continue;
            }

            $hasil = $this->prosesSatuBaris($rowData, $nomorBarisData, $tahunAktif);

            if ($hasil['sukses']) {
                $suksesBatch++;
            } else {
                $gagalBatch++;
                $errorBatch[] = $hasil['pesan'];
            }
        }

        $barisSelesaiBaru = $barisSelesaiBatch - 1; // konversi balik ke jumlah baris data yang sudah diproses
        $totalGagalLama = json_decode($job['detail_gagal'] ?? '[]', true) ?: [];
        $totalGagalBaru = array_merge($totalGagalLama, $errorBatch);

        $statusBaru = $barisSelesaiBaru >= $job['total_baris'] ? 'Selesai' : 'Proses';

        $this->importLogModel->update($id_import_log, [
            'baris_selesai' => $barisSelesaiBaru,
            'total_sukses'  => $job['total_sukses'] + $suksesBatch,
            'total_gagal'   => $job['total_gagal'] + $gagalBatch,
            'detail_gagal'  => json_encode($totalGagalBaru),
            'status'        => $statusBaru,
        ]);

        return $this->response->setJSON(array_merge([
            'selesai'       => $statusBaru === 'Selesai',
            'baris_selesai' => $barisSelesaiBaru,
            'total_baris'   => $job['total_baris'],
            'total_sukses'  => $job['total_sukses'] + $suksesBatch,
            'total_gagal'   => $job['total_gagal'] + $gagalBatch,
        ], $this->csrfData()));
    }

    // Proses 1 baris siswa — transaksi sendiri, gagal di sini tidak pengaruhi baris lain
    protected function prosesSatuBaris(array $data, int $nomorBaris, array $tahunAktif): array
    {
        // Validasi wajib
        if (empty($data['nis']) || empty($data['nisn']) || empty($data['nama'])) {
            return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: NIS/NISN/Nama kosong, dilewati."];
        }

        if (!in_array($data['jenis_kelamin'], ['L', 'P'])) {
            return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: Jenis Kelamin '{$data['jenis_kelamin']}' tidak valid (harus L/P)."];
        }

        // Validasi format email kalau diisi — skipValidation(true) di bawah membuat rule model
        // tidak jalan, jadi perlu dicek manual di sini
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: Format email '{$data['email']}' tidak valid."];
        }

        // Cek duplikat NIS/NISN
        if ($this->siswaModel->where('nis', $data['nis'])->first()) {
            return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: NIS '{$data['nis']}' sudah terdaftar, dilewati."];
        }
        if ($this->siswaModel->where('nisn', $data['nisn'])->first()) {
            return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: NISN '{$data['nisn']}' sudah terdaftar, dilewati."];
        }

        // Cari kelas berdasarkan nama persis, di tahun ajaran aktif
        $kelas = $this->kelasModel->where('nama_kelas', $data['kelas'])
            ->where('id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
            ->first();
        if (!$kelas) {
            return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: Kelas '{$data['kelas']}' tidak ditemukan di Tahun Ajaran Aktif."];
        }

        // Normalisasi tanggal lahir
        $tanggalLahir = $this->normalisasiTanggal($data['tanggal_lahir']);
        if (!$tanggalLahir) {
            return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: Format Tanggal Lahir tidak dikenali."];
        }

        $passwordAwal = date('dmY', strtotime($tanggalLahir));

        $db = Database::connect();

        try {
            $db->transStart();

            // --- Insert user ---
            $userId = $this->userModel->skipValidation(true)->insert([
                'username'             => $data['nis'],
                'password'             => password_hash($passwordAwal, PASSWORD_DEFAULT),
                'role_id'              => 3,
                'status'               => 'Aktif',
                'must_change_password' => true,
            ]);

            if (!$userId) {
                $db->transRollback();
                $errors = $this->userModel->errors();
                $pesanError = $errors ? implode('; ', $errors) : ($db->error()['message'] ?? 'unknown error');
                return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: Gagal buat akun user - {$pesanError}"];
            }

            // --- Insert siswa ---
            $idSiswa = $this->siswaModel->skipValidation(true)->insert([
                'user_id'        => $userId,
                'nis'            => $data['nis'],
                'nisn'           => $data['nisn'],
                'nama'           => $data['nama'],
                'tempat_lahir'   => $data['tempat_lahir'],
                'tanggal_lahir'  => $tanggalLahir,
                'jenis_kelamin'  => $data['jenis_kelamin'],
                'agama'          => $data['agama'],
                'alamat'         => $data['alamat'],
                'nama_ayah'      => $data['nama_ayah'],
                'nama_ibu'       => $data['nama_ibu'],
                'pekerjaan_ortu' => $data['pekerjaan_ortu'],
                'no_hp_ortu'     => $data['no_hp_ortu'],
                'email'          => $data['email'] ?: null,
                'status'         => 'Aktif',
            ]);

            if (!$idSiswa) {
                $db->transRollback();
                $errors = $this->siswaModel->errors();
                $pesanError = $errors ? implode('; ', $errors) : ($db->error()['message'] ?? 'unknown error');
                return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: Gagal buat data siswa - {$pesanError}"];
            }

            // --- Insert kelas_siswa ---
            $db->table('kelas_siswa')->insert([
                'id_kelas'        => $kelas['id_kelas'],
                'id_siswa'        => $idSiswa,
                'id_tahun_ajaran' => $tahunAktif['id_tahun_ajaran'],
                'created_at'      => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: Gagal insert ke database (transaksi rollback)."];
            }

            return ['sukses' => true, 'pesan' => ''];
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['sukses' => false, 'pesan' => "Baris {$nomorBaris}: Terjadi error tak terduga - " . $e->getMessage()];
        }
    }

    // Deteksi 3 kemungkinan format tanggal → convert ke Y-m-d
    protected function normalisasiTanggal($cell): ?string
    {
        if ($cell->getDataType() === DataType::TYPE_NUMERIC) {
            try {
                $dateObj = ExcelDate::excelToDateTimeObject($cell->getValue());
                return $dateObj->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $value = trim((string) $cell->getValue());
        if (empty($value)) {
            return null;
        }

        // Coba format YYYY-MM-DD
        $d = \DateTime::createFromFormat('Y-m-d', $value);
        if ($d && $d->format('Y-m-d') === $value) {
            return $value;
        }

        // Coba format DD/MM/YYYY
        $d = \DateTime::createFromFormat('d/m/Y', $value);
        if ($d) {
            return $d->format('Y-m-d');
        }

        return null;
    }

    // Cek status job (dipakai buat progress bar & deteksi resume)
    public function status($id_import_log)
    {
        $job = $this->importLogModel->find($id_import_log);
        if (!$job) {
            return $this->response->setJSON(['error' => 'Job tidak ditemukan.']);
        }

        return $this->response->setJSON([
            'baris_selesai' => $job['baris_selesai'],
            'total_baris'   => $job['total_baris'],
            'total_sukses'  => $job['total_sukses'],
            'total_gagal'   => $job['total_gagal'],
            'status'        => $job['status'],
            'detail_gagal'  => json_decode($job['detail_gagal'] ?? '[]', true),
        ]);
    }
}