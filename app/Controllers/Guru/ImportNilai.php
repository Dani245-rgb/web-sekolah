<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Libraries\Import\Nilai\NilaiRepository;
use App\Libraries\Import\Nilai\NilaiValidator;
use App\Libraries\Import\Nilai\NilaiImportService;
use App\Libraries\Import\Nilai\NilaiRollbackService;
use App\Models\GuruModel;
use CodeIgniter\Files\File;

class ImportNilai extends BaseController
{
    protected NilaiRepository $repo;
    protected NilaiRollbackService $rollbackService;

    public function __construct()
    {
        $this->repo = new NilaiRepository();
        $this->rollbackService = new NilaiRollbackService($this->repo);
    }

    protected function getGuruLogin()
    {
        $userId    = session()->get('id_user');
        $guruModel = new GuruModel();
        $guru      = $guruModel->where('user_id', $userId)->first();

        if (!$guru) {
            throw new \RuntimeException('DATA_GURU_TIDAK_DITEMUKAN');
        }

        return $guru;
    }

    /**
     * Ambil id_kelas & id_tahun_ajaran dari id_komponen yang dipilih.
     */
    protected function resolveKonteks(int $idKomponen): array
    {
        $guru = $this->getGuruLogin();

        $komponen = $this->repo->findKomponen($idKomponen);
        if (!$komponen) {
            throw new \RuntimeException('Komponen nilai tidak ditemukan');
        }

        $db = $this->repo->getDb();
        $pengaturan = $db->table('pengaturan_nilai')
            ->where('id_pengaturan', $komponen['id_pengaturan'])
            ->get()->getRowArray();

        if (!$pengaturan) {
            throw new \RuntimeException('Pengaturan nilai tidak ditemukan');
        }

        // Cegah IDOR: komponen ini harus milik guru yang sedang login
        if ((int) $pengaturan['id_guru'] !== (int) $guru['id_guru']) {
            throw new \RuntimeException('Komponen nilai ini bukan milik Anda.');
        }

        $semester = $db->table('semester')
            ->where('id_semester', $pengaturan['id_semester'])
            ->get()->getRowArray();

        if (!$semester) {
            throw new \RuntimeException('Semester tidak ditemukan');
        }

        return [
            'id_kelas'        => $pengaturan['id_kelas'],
            'id_tahun_ajaran' => $semester['id_tahun_ajaran'],
        ];
    }

    /**
     * GET /guru/nilai/import/template/(:num)
     * Download template Excel berisi daftar siswa di kelas komponen ini.
     */
    public function template(int $idKomponen)
    {
        $konteks = $this->resolveKonteks($idKomponen);

        $db = $this->repo->getDb();
        $siswaList = $db->table('siswa s')
            ->select('s.id_siswa, s.nis, s.nama')
            ->join('kelas_siswa ks', 'ks.id_siswa = s.id_siswa')
            ->where('ks.id_kelas', $konteks['id_kelas'])
            ->where('ks.id_tahun_ajaran', $konteks['id_tahun_ajaran'])
            ->orderBy('s.nama', 'ASC')
            ->get()->getResultArray();

        // Ambil nilai yang sudah ada untuk komponen ini, supaya template ke-prefill
        $nilaiExisting = $db->table('nilai_siswa')
            ->where('id_komponen', $idKomponen)
            ->get()->getResultArray();

        $nilaiMap = [];
        foreach ($nilaiExisting as $n) {
            $nilaiMap[$n['id_siswa']] = $n['nilai'];
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'ID Siswa');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Nama');
        $sheet->setCellValue('D1', 'Nilai');
        $sheet->setCellValue('E1', 'JanganDiubah'); // kolom internal, jangan diedit guru

        $baris = 2;
        foreach ($siswaList as $s) {
            $sheet->setCellValue("A{$baris}", $s['id_siswa']);
            $sheet->setCellValue("B{$baris}", $s['nis']);
            $sheet->setCellValue("C{$baris}", $s['nama']);

            // Prefill nilai yang sudah ada, kalau belum ada dikosongkan
            if (isset($nilaiMap[$s['id_siswa']])) {
                $sheet->setCellValue("D{$baris}", $nilaiMap[$s['id_siswa']]);
                // Snapshot: nilai yang ada di database SAAT template ini dibuat
                $sheet->setCellValue("E{$baris}", $nilaiMap[$s['id_siswa']]);
            } else {
                // Snapshot: belum ada nilai sama sekali saat template ini dibuat
                $sheet->setCellValue("E{$baris}", 'KOSONG');
            }

            $baris++;
        }

        // Sembunyikan kolom E dari tampilan guru — ini cuma jejak internal sistem
        $sheet->getColumnDimension('E')->setVisible(false);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $namaFile = 'template_nilai_' . $idKomponen . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $namaFile . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * POST /guru/nilai/import/preview
     * Upload file, jalankan validasi, tampilkan preview (belum simpan ke DB).
     */
    public function preview()
    {
        $idKomponen = (int) $this->request->getPost('id_komponen');
        $file = $this->request->getFile('file_excel');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid');
        }

        if (!in_array($file->getClientExtension(), ['xlsx', 'xls'])) {
            return redirect()->back()->with('error', 'File harus format .xlsx atau .xls');
        }

        $konteks = $this->resolveKonteks($idKomponen);

        // Cari id_jadwal yang cocok (id_kelas + id_mapel + id_guru dari pengaturan_nilai)
        $komponen = $this->repo->findKomponen($idKomponen);
        $db = $this->repo->getDb();
        $pengaturan = $db->table('pengaturan_nilai')
            ->where('id_pengaturan', $komponen['id_pengaturan'])
            ->get()->getRowArray();

        $jadwalRow = $db->table('jadwal')
            ->where('id_kelas', $pengaturan['id_kelas'])
            ->where('id_mapel', $pengaturan['id_mapel'])
            ->where('id_guru', $pengaturan['id_guru'])
            ->get()->getRowArray();

        $idJadwal = $jadwalRow['id_jadwal'] ?? null;

        $namaFileAsli = $file->getClientName();
        $pathTemp = WRITEPATH . 'uploads/' . $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', basename($pathTemp));

        $validator = new NilaiValidator($this->repo, $idKomponen, $konteks['id_kelas'], $konteks['id_tahun_ajaran']);
        $service = new NilaiImportService($this->repo, $validator, $idKomponen);

        $result = $service->preview($pathTemp);

        // simpan hasil preview + path file sementara ke session, dipakai pas konfirmasi
        session()->set('import_nilai_preview', [
            'id_komponen'   => $idKomponen,
            'id_jadwal'     => $idJadwal,
            'path_file'     => $pathTemp,
            'nama_file'     => $namaFileAsli,
            'rows'          => serialize($result->rows),
            'ringkasan'     => $result->ringkasan(),
        ]);

        return view('guru/nilai/preview_import', [
            'result'      => $result,
            'idKomponen'  => $idKomponen,
            'idJadwal'    => $idJadwal,
            'namaFile'    => $namaFileAsli,
        ]);
    }

    /**
     * POST /guru/nilai/import/konfirmasi
     * Simpan hasil import ke database (dipanggil setelah guru cek preview).
     */
    public function konfirmasi()
    {
        $data = session()->get('import_nilai_preview');
        if (!$data) {
            return redirect()->to('/guru/nilai')->with('error', 'Sesi import kadaluarsa, silakan upload ulang');
        }

        $lewatiBarisGagal = (bool) $this->request->getPost('lewati_baris_gagal');
        $timpaKonflik     = (bool) $this->request->getPost('timpa_konflik');
        $idUser = session()->get('id_user');

        $konteks = $this->resolveKonteks($data['id_komponen']);
        $validator = new NilaiValidator($this->repo, $data['id_komponen'], $konteks['id_kelas'], $konteks['id_tahun_ajaran']);
        $service = new NilaiImportService($this->repo, $validator, $data['id_komponen']);

        // re-run parse+validate (state paling akurat, bukan trust session sepenuhnya)
        $result = $service->preview($data['path_file']);

        try {
            $result = $service->confirm($result, $lewatiBarisGagal, $idUser, $data['nama_file'], $data['id_komponen'], $timpaKonflik);
        } catch (\RuntimeException $e) {
            return view('guru/nilai/preview_import', [
                'result'      => $result,
                'idKomponen'  => $data['id_komponen'],
                'idJadwal'    => $data['id_jadwal'],
                'namaFile'    => $data['nama_file'],
                'error'       => $e->getMessage(),
            ]);
        }

        @unlink($data['path_file']);
        session()->remove('import_nilai_preview');

        return redirect()->to('/guru/nilai/form/' . $data['id_jadwal'])
            ->with('success', "Import selesai: {$result->totalValid} valid, {$result->totalDitimpa} ditimpa, {$result->totalGagal} gagal");
    }

    /**
     * POST /guru/nilai/import/rollback/(:num)
     * Tidak lagi lewat NilaiImportService — rollback murni pakai NilaiRollbackService,
     * jadi tidak perlu bikin NilaiValidator kosongan seperti versi sebelumnya.
     */
    public function rollback(int $idImportLog)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }
        $idUser = session()->get('id_user');

        // Cegah IDOR: pastikan log import ini milik guru yang login
        $db = $this->repo->getDb();
        $log = $db->table('import_log')->where('id_import_log', $idImportLog)->get()->getRowArray();

        if (!$log || (int) $log['id_user'] !== (int) $idUser) {
            return redirect()->back()->with('error', 'Riwayat import ini bukan milik Anda.');
        }

        try {
            $laporan = $this->rollbackService->rollback($idImportLog, $idUser);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        $pesan = "{$laporan['berhasil']} baris berhasil di-rollback";
        if (!empty($laporan['dilewati'])) {
            $pesan .= ", " . count($laporan['dilewati']) . " baris dilewati (sudah diubah manual)";
        }

        return redirect()->back()->with('success', $pesan);
    }
}
