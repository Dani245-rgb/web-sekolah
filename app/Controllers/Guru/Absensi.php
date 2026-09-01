<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\JadwalModel;
use App\Models\KelasSiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\AbsensiJadwalModel;
use App\Models\AbsensiDetailModel;
use App\Models\AuditLogModel;
use App\Models\SiswaModel;
use App\Models\HariKhususModel;
use App\Libraries\WhatsappService;

class Absensi extends BaseController
{
    /**
     * Ambil data guru yang sedang login.
     * Melempar RuntimeException kalau data guru tidak ditemukan
     * (misal akun user ada tapi data guru sudah terhapus).
     */
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

    public function form($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        $kelasSiswaModel = new KelasSiswaModel();
        $siswaList = $tahunAktif
            ? $kelasSiswaModel->getSiswaByKelas($jadwal['id_kelas'], $tahunAktif['id_tahun_ajaran'])
            : [];

        $absensiJadwalModel = new AbsensiJadwalModel();
        $absensiDetailModel = new AbsensiDetailModel();
        $existing = $absensiJadwalModel->cariByJadwalTanggal($idJadwal, $tanggal);

        $statusTersimpan = [];
        if ($existing) {
            $detail = $absensiDetailModel->where('id_absensi_jadwal', $existing['id_absensi_jadwal'])->findAll();
            foreach ($detail as $d) {
                $statusTersimpan[$d['id_siswa']] = ['status' => $d['status'], 'keterangan' => $d['keterangan']];
            }
        }

        $data['jadwal']          = $jadwal;
        $data['tanggal']         = $tanggal;
        $data['siswaList']       = $siswaList;
        $data['statusTersimpan'] = $statusTersimpan;
        $data['mode']            = $existing ? 'edit' : 'baru';

        $hariKhususModel = new HariKhususModel();
        $data['hariKhususHariIni'] = $hariKhususModel->cariByTanggalJadwal($idJadwal, $tanggal);

        // Data untuk Peta Absensi (rekap mingguan bulan berjalan)
        $bulanPeta = (int) ($this->request->getGet('bulan') ?: date('n'));
        $tahunPeta = (int) ($this->request->getGet('tahun') ?: date('Y'));
        $tanggalMingguan = $this->getTanggalSesuaiHari($jadwal['hari'], $bulanPeta, $tahunPeta);

        $matrix = [];
        if (!empty($tanggalMingguan)) {
            $rows = $absensiDetailModel->getMatrixByJadwal($idJadwal, $tanggalMingguan);
            foreach ($rows as $r) {
                $matrix[$r['id_siswa']][$r['tanggal']] = $r['status'];
            }
        }

        $mapHariKhusus = $hariKhususModel->getMapByTanggalList($idJadwal, $tanggalMingguan);

        $data['bulanPeta']       = $bulanPeta;
        $data['tahunPeta']       = $tahunPeta;
        $data['tanggalMingguan'] = $tanggalMingguan;
        $data['matrix']          = $matrix;
        $data['mapHariKhusus']   = $mapHariKhusus;

        return view('guru/absensi/form', $data);
    }

    public function simpan()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $idJadwal = $this->request->getPost('id_jadwal');
        $tanggal  = $this->request->getPost('tanggal');
        $statusArr = $this->request->getPost('status');
        $keteranganArr = $this->request->getPost('keterangan');

        // Pengaman: kalau gak ada siswa yang dikirim (kelas kosong / form rusak)
        if (empty($statusArr) || !is_array($statusArr)) {
            return redirect()->back()->with('errors', ['kosong' => 'Tidak ada data siswa untuk disimpan. Pastikan kelas ini sudah punya siswa yang di-assign.']);
        }

        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->find($idJadwal);
        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || !strtotime($tanggal)) {
            return redirect()->back()->with('errors', ['tanggal' => 'Format tanggal tidak valid.']);
        }

        // Validasi status hanya boleh nilai yang diizinkan (cegah data sampah/manipulasi)
        $statusValid = ['Hadir', 'Izin', 'Sakit', 'Alfa'];
        foreach ($statusArr as $status) {
            if (!in_array($status, $statusValid, true)) {
                return redirect()->back()->with('errors', ['status' => "Status '{$status}' tidak valid."]);
            }
        }

        // Validasi id_siswa yang dikirim benar-benar siswa di kelas jadwal ini (cegah manipulasi/IDOR)
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();
        $kelasSiswaModel = new KelasSiswaModel();
        $siswaValid = $tahunAktif
            ? array_column($kelasSiswaModel->getSiswaByKelas($jadwal['id_kelas'], $tahunAktif['id_tahun_ajaran']), 'id_siswa')
            : [];
        $siswaValid = array_flip($siswaValid);

        foreach (array_keys($statusArr) as $idSiswa) {
            if (!isset($siswaValid[$idSiswa])) {
                return redirect()->back()->with('errors', ['siswa' => 'Ada data siswa yang tidak terdaftar di kelas ini, absensi tidak disimpan.']);
            }
        }

        $absensiJadwalModel = new AbsensiJadwalModel();
        $absensiDetailModel = new AbsensiDetailModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $existing = $absensiJadwalModel->cariByJadwalTanggal($idJadwal, $tanggal);
        if ($existing) {
            $idAbsensiJadwal = $existing['id_absensi_jadwal'];
            $absensiDetailModel->where('id_absensi_jadwal', $idAbsensiJadwal)->delete();
        } else {
            $idAbsensiJadwal = $absensiJadwalModel->insert([
                'id_jadwal'  => $idJadwal,
                'tanggal'    => $tanggal,
                'id_guru'    => $guru['id_guru'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        foreach ($statusArr as $idSiswa => $status) {
            $absensiDetailModel->insert([
                'id_absensi_jadwal' => $idAbsensiJadwal,
                'id_siswa'          => $idSiswa,
                'status'            => $status,
                'keterangan'        => $keteranganArr[$idSiswa] ?? null,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, absensi tidak tersimpan.']);
        }

        // Ambil nama siswa yang beneran (jangan andalkan $siswaList, itu cuma ada di method form())
        $siswaModel = new \App\Models\SiswaModel();
        $namaSiswaMap = [];
        if (!empty($statusArr)) {
            $siswaRows = $siswaModel->whereIn('id_siswa', array_keys($statusArr))->findAll();
            foreach ($siswaRows as $s) {
                $namaSiswaMap[$s['id_siswa']] = $s['nama'];
            }
        }

        // Catat riwayat perubahan (v1.1 - pakai nama siswa asli, format lebih mudah di-parse)
        $rekap = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
        $tidakHadir = [];
        foreach ($statusArr as $idSiswa => $status) {
            if (isset($rekap[$status])) {
                $rekap[$status]++;
            }
            if ($status !== 'Hadir') {
                $namaSiswa = $namaSiswaMap[$idSiswa] ?? "Siswa#{$idSiswa}";
                $tidakHadir[] = "{$namaSiswa} ({$status})";
            }
        }

        $jadwalLengkap = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        $ringkasan = "Jadwal:{$idJadwal}|Tanggal:{$tanggal}|Kelas:{$jadwalLengkap['nama_kelas']}|Mapel:{$jadwalLengkap['nama_mapel']}"
            . "|Hadir:{$rekap['Hadir']}|Izin:{$rekap['Izin']}|Sakit:{$rekap['Sakit']}|Alfa:{$rekap['Alfa']}"
            . "|TidakHadir:" . (empty($tidakHadir) ? '-' : implode(', ', $tidakHadir));

        $auditLogModel = new AuditLogModel();
        $auditLogModel->catat(
            session()->get('id_user'),
            $guru['nama'],
            'Isi Absensi',
            $ringkasan
        );

        // $this->kirimNotifAbsensi($statusArr, $keteranganArr, $jadwalLengkap, $tanggal);
         // TODO: aktifkan setelah WhatsappService dibuat

        return redirect()->to('/guru/dashboard')->with('success', 'Absensi berhasil disimpan.');
    }

    /**
     * Kirim notif WA ke ortu untuk siswa dengan status selain Hadir.
     * Best-effort: kegagalan kirim (nomor kosong/API error) tidak menggagalkan proses absensi.
     */
    protected function kirimNotifAbsensi(array $statusArr, array $keteranganArr, array $jadwal, string $tanggal): void
    {
        $siswaModel = new SiswaModel();
        $wa = new WhatsappService();

        $idSiswaTidakHadir = [];
        foreach ($statusArr as $idSiswa => $status) {
            if ($status !== 'Hadir') {
                $idSiswaTidakHadir[] = $idSiswa;
            }
        }

        if (empty($idSiswaTidakHadir)) {
            return;
        }

        $siswaRows = $siswaModel->whereIn('id_siswa', $idSiswaTidakHadir)->findAll();

        foreach ($siswaRows as $s) {
            $status = $statusArr[$s['id_siswa']];
            $ket = $keteranganArr[$s['id_siswa']] ?? null;
            $tanggalFormatted = date('d M Y', strtotime($tanggal));

            $pesan = "Yth. Orang Tua/Wali dari {$s['nama']},\n"
                . "Kami informasikan bahwa ananda tercatat *{$status}* pada mata pelajaran {$jadwal['nama_mapel']} "
                . "tanggal {$tanggalFormatted}."
                . ($ket ? "\nKeterangan: {$ket}" : '')
                . "\n\nTerima kasih.";

            $refKey = "absensi-{$s['id_siswa']}-{$tanggal}-{$jadwal['id_jadwal']}";
            $wa->kirimKeOrtu($s, 'absensi', $pesan, $refKey);
        }
    }

    /**
     * GET /guru/absensi/riwayat/(:num)
     * Tampilkan riwayat pengisian absensi untuk jadwal ini,
     * diambil dari audit_log (aksi = 'Isi Absensi'), difilter berdasarkan id_jadwal
     * yang dititipkan di dalam teks keterangan.
     */
    public function riwayat($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $auditLogModel = new AuditLogModel();
        $riwayatList = $auditLogModel
            ->where('user_id', session()->get('id_user'))
            ->where('aksi', 'Isi Absensi')
            ->like('keterangan', "Jadwal:{$idJadwal}|", 'after')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('guru/absensi/riwayat', [
            'jadwal'      => $jadwal,
            'riwayatList' => $riwayatList,
        ]);
    }

    public function peta($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $bulan = (int) ($this->request->getGet('bulan') ?: date('n'));
        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));

        // Semua tanggal dalam bulan ini yang jatuh di hari yang sama dengan jadwal (mis. semua hari Rabu)
        $tanggalMingguan = $this->getTanggalSesuaiHari($jadwal['hari'], $bulan, $tahun);

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        $kelasSiswaModel = new KelasSiswaModel();
        $siswaList = $tahunAktif
            ? $kelasSiswaModel->getSiswaByKelas($jadwal['id_kelas'], $tahunAktif['id_tahun_ajaran'])
            : [];

        $absensiDetailModel = new AbsensiDetailModel();
        $matrix = [];

        if (!empty($tanggalMingguan)) {
            $rows = $absensiDetailModel->getMatrixByJadwal($idJadwal, $tanggalMingguan);
            foreach ($rows as $r) {
                $matrix[$r['id_siswa']][$r['tanggal']] = $r['status'];
            }
        }

        return view('guru/absensi/peta', [
            'jadwal'          => $jadwal,
            'siswaList'       => $siswaList,
            'tanggalMingguan' => $tanggalMingguan,
            'matrix'          => $matrix,
            'bulan'           => $bulan,
            'tahun'           => $tahun,
        ]);
    }

        public function exportPdf($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $bulan = (int) ($this->request->getGet('bulan') ?: date('n'));
        $tahun = (int) ($this->request->getGet('tahun') ?: date('Y'));

        $tanggalMingguan = $this->getTanggalSesuaiHari($jadwal['hari'], $bulan, $tahun);

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        $kelasSiswaModel = new KelasSiswaModel();
        $siswaList = $tahunAktif
            ? $kelasSiswaModel->getSiswaByKelas($jadwal['id_kelas'], $tahunAktif['id_tahun_ajaran'])
            : [];

        $absensiDetailModel = new AbsensiDetailModel();
        $matrix = [];

        if (!empty($tanggalMingguan)) {
            $rows = $absensiDetailModel->getMatrixByJadwal($idJadwal, $tanggalMingguan);
            foreach ($rows as $r) {
                $matrix[$r['id_siswa']][$r['tanggal']] = $r['status'];
            }
        }

        // Hitung rekap per siswa (logic sama persis dengan yang ada di view peta.php)
        $rekapBulanIni = [];
        foreach ($siswaList as $s) {
            $rekapBulanIni[$s['id_siswa']] = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
            foreach ($tanggalMingguan as $tgl) {
                $status = $matrix[$s['id_siswa']][$tgl] ?? null;
                if ($status && isset($rekapBulanIni[$s['id_siswa']][$status])) {
                    $rekapBulanIni[$s['id_siswa']][$status]++;
                }
            }
        }

        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        // Kelompokkan tanggal pertemuan berdasarkan minggu kalender (Minggu 1 = tanggal 1-7, dst)
        $kelompokMinggu = [];
        foreach ($tanggalMingguan as $tgl) {
            $tanggalKe = (int) date('j', strtotime($tgl));
            $mingguKe  = (int) ceil($tanggalKe / 7);
            $kelompokMinggu[$mingguKe][] = $tgl;
        }

        $html = view('guru/absensi/pdf', [
            'jadwal'          => $jadwal,
            'siswaList'       => $siswaList,
            'tanggalMingguan' => $tanggalMingguan,
            'kelompokMinggu'  => $kelompokMinggu,
            'matrix'          => $matrix,
            'rekapBulanIni'   => $rekapBulanIni,
            'namaBulanTeks'   => $namaBulan[$bulan] ?? '-',
            'tahun'           => $tahun,
        ]);

        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']); // Landscape, biar muat kolom banyak
        $mpdf->SetTitle('Rekap Absensi');
        $mpdf->WriteHTML($html);

        $namaKelasAman = $this->sanitasiNamaFile($jadwal['nama_kelas']);
        $namaMapelAman = $this->sanitasiNamaFile($jadwal['nama_mapel']);
        $mpdf->Output("rekap-absensi_{$namaKelasAman}_{$namaMapelAman}_{$namaBulan[$bulan]}-{$tahun}.pdf", 'D');
    }

    /**
     * Sanitasi nama file, hindari karakter aneh masuk ke header Content-Disposition.
     */
    protected function sanitasiNamaFile(string $str): string
    {
        $str = preg_replace('/[^A-Za-z0-9\s\-_]/', '', $str);
        return trim($str);
    }

    /**
     * Cari semua tanggal dalam sebuah bulan yang jatuh pada hari tertentu
     * (misal semua tanggal "Rabu" di bulan itu), sesuai hari jadwal mengajar.
     */
    protected function getTanggalSesuaiHari(string $hariJadwal, int $bulan, int $tahun): array
    {
        $hariMapBalik = [
            'Senin' => 'Monday',
            'Selasa' => 'Tuesday',
            'Rabu' => 'Wednesday',
            'Kamis' => 'Thursday',
            'Jumat' => 'Friday',
            'Sabtu' => 'Saturday',
            'Minggu' => 'Sunday',
        ];
        $hariEn = $hariMapBalik[$hariJadwal] ?? null;
        if (!$hariEn) return [];

        $tanggalList = [];
        $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        for ($d = 1; $d <= $jumlahHari; $d++) {
            $ts = mktime(0, 0, 0, $bulan, $d, $tahun);
            if (date('l', $ts) === $hariEn) {
                $tanggalList[] = date('Y-m-d', $ts);
            }
        }

        return $tanggalList;
    }

    /**
     * Guru menandai tanggal tertentu (untuk jadwal ini saja) sebagai hari khusus.
     */
    public function tandaiKhusus()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $idJadwal   = $this->request->getPost('id_jadwal');
        $tanggal    = $this->request->getPost('tanggal');
        $keterangan = trim((string) $this->request->getPost('keterangan'));

        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->find($idJadwal);
        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        if ($keterangan === '') {
            return redirect()->back()->with('errors', ['keterangan' => 'Keterangan hari khusus wajib diisi (mis. Rapat Dadakan).']);
        }

        $hariKhususModel = new HariKhususModel();

        // Cegah duplikat utk jadwal+tanggal yg sama
        $existing = $hariKhususModel->where('id_jadwal', $idJadwal)->where('tanggal', $tanggal)->first();
        if ($existing) {
            $hariKhususModel->update($existing['id_hari_khusus'], ['keterangan' => $keterangan]);
        } else {
            $hariKhususModel->insert([
                'tanggal'    => $tanggal,
                'keterangan' => $keterangan,
                'id_jadwal'  => $idJadwal,
                'id_guru'    => $guru['id_guru'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/guru/absensi/form/' . $idJadwal . '?tanggal=' . $tanggal)
            ->with('success', 'Tanggal ini ditandai sebagai hari khusus.');
    }

    /**
     * Guru membatalkan penandaan hari khusus (hanya yang dia buat sendiri, bukan yg dari Admin).
     */
    public function hapusKhusus()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $idJadwal = $this->request->getPost('id_jadwal');
        $tanggal  = $this->request->getPost('tanggal');

        $hariKhususModel = new HariKhususModel();
        $hariKhususModel
            ->where('id_jadwal', $idJadwal) // hanya hapus yg id_jadwal spesifik (bukan yg null/global punya Admin)
            ->where('tanggal', $tanggal)
            ->where('id_guru', $guru['id_guru'])
            ->delete();

        return redirect()->to('/guru/absensi/form/' . $idJadwal . '?tanggal=' . $tanggal)
            ->with('success', 'Penandaan hari khusus dibatalkan.');
    }
}
