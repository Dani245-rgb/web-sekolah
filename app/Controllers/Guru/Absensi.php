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

        $this->kirimNotifAbsensi($statusArr, $keteranganArr, $jadwalLengkap, $tanggal);

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
}