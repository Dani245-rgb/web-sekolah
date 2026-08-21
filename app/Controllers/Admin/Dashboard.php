<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\GuruModel;
use App\Models\KelasModel;
use App\Models\MapelModel;
use App\Models\TahunAjaranModel;
use App\Models\SemesterModel;
use App\Models\JadwalModel;
use App\Models\PengumumanModel;
use Config\Database;

class Dashboard extends BaseController
{
    public function index()
    {
        $tahunAjaranModel = new TahunAjaranModel();
        $siswaModel       = new SiswaModel();
        $guruModel        = new GuruModel();
        $kelasModel       = new KelasModel();
        $mapelModel       = new MapelModel();
        $semesterModel    = new SemesterModel();
        $jadwalModel      = new JadwalModel();
        $pengumumanModel  = new PengumumanModel();
        $db               = Database::connect();

        $tahunAktif   = $tahunAjaranModel->getActive();
        $idTahunAktif = $tahunAktif['id_tahun_ajaran'] ?? 0;

        // Statistik ringkas
        $totalSiswaAktif = $siswaModel->where('status', 'Aktif')->countAllResults();
        $totalGuru       = $guruModel->countAllResults();
        $totalKelas      = $kelasModel->where('status', 'Aktif')
            ->where('id_tahun_ajaran', $idTahunAktif)
            ->countAllResults();
        $totalMapel      = $mapelModel->where('status', 'Aktif')->countAllResults();

        // Siswa baru per tahun ajaran
        $siswaBaruPerTahunRaw = $db->query("
            SELECT ta.tahun_ajaran, COUNT(*) as jumlah
            FROM (
                SELECT id_siswa, MIN(id_tahun_ajaran) as first_tahun
                FROM kelas_siswa
                GROUP BY id_siswa
            ) first_entry
            JOIN tahun_ajaran ta ON ta.id_tahun_ajaran = first_entry.first_tahun
            GROUP BY ta.id_tahun_ajaran, ta.tahun_ajaran
            ORDER BY ta.id_tahun_ajaran ASC
        ")->getResultArray();

        $chartLabels = array_column($siswaBaruPerTahunRaw, 'tahun_ajaran');
        $chartData   = array_map('intval', array_column($siswaBaruPerTahunRaw, 'jumlah'));

        $siswaBaruTahunIni = 0;
        foreach ($siswaBaruPerTahunRaw as $row) {
            if (!empty($tahunAktif) && $row['tahun_ajaran'] === $tahunAktif['tahun_ajaran']) {
                $siswaBaruTahunIni = (int) $row['jumlah'];
            }
        }

        // Siswa aktif per jurusan
        $perJurusanRaw = $db->table('siswa')
            ->select('kelas.jurusan, COUNT(*) as jumlah')
            ->join('kelas_siswa', 'kelas_siswa.id_siswa = siswa.id_siswa AND kelas_siswa.id_tahun_ajaran = ' . (int) $idTahunAktif)
            ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas')
            ->where('siswa.status', 'Aktif')
            ->groupBy('kelas.jurusan')
            ->get()->getResultArray();

        $perJurusan = [];
        foreach ($perJurusanRaw as $row) {
            $perJurusan[$row['jurusan']] = (int) $row['jumlah'];
        }

        // Siswa aktif per gender
        $perGenderRaw = $siswaModel->select('jenis_kelamin, COUNT(*) as jumlah')
            ->where('status', 'Aktif')
            ->groupBy('jenis_kelamin')
            ->findAll();

        $perGender = ['L' => 0, 'P' => 0];
        foreach ($perGenderRaw as $row) {
            $perGender[$row['jenis_kelamin']] = (int) $row['jumlah'];
        }

        // Semua siswa per status
        $perStatusRaw = $siswaModel->select('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->findAll();

        $perStatus = [];
        foreach ($perStatusRaw as $row) {
            $perStatus[$row['status']] = (int) $row['jumlah'];
        }

        // 5 notifikasi terbaru
        $notifikasiTerbaru = $db->table('notifikasi')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // ===== BAGIAN BARU =====

        // Jadwal Hari Ini
        $mapHari  = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $hariIni  = $mapHari[(int) date('N')];
        $semesterAktif = $semesterModel->getActive();

        $jadwalHariIni = [];
        if ($semesterAktif) {
            $jadwalHariIni = array_slice(
                $jadwalModel->getAllWithRelasi($semesterAktif['id_semester'], ['hari' => $hariIni]),
                0,
                5
            );
        }

        // Absensi Hari Ini (rekap semua kelas)
        $tanggalHariIni  = date('Y-m-d');
        $rekapAbsensiRaw = $db->table('absensi_detail')
            ->select('absensi_detail.status, COUNT(*) as jumlah')
            ->join('absensi_jadwal', 'absensi_jadwal.id_absensi_jadwal = absensi_detail.id_absensi_jadwal')
            ->where('absensi_jadwal.tanggal', $tanggalHariIni)
            ->groupBy('absensi_detail.status')
            ->get()->getResultArray();

        $rekapAbsensiHariIni = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
        foreach ($rekapAbsensiRaw as $row) {
            $rekapAbsensiHariIni[$row['status']] = (int) $row['jumlah'];
        }
        $totalAbsensiHariIni = array_sum($rekapAbsensiHariIni);
        $persenHadirHariIni  = $totalAbsensiHariIni > 0
            ? round($rekapAbsensiHariIni['Hadir'] / $totalAbsensiHariIni * 100, 1)
            : 0;

        // Pengumuman Terbaru
        $pengumumanTerbaru = $pengumumanModel->where('status', 'Published')
            ->orderBy('tanggal_publish', 'DESC')
            ->findAll(5);

        // Aktivitas Terbaru (Audit Log)
        $aktivitasTerbaru = $db->table('audit_log')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $data = [
            'title'               => 'Dashboard',
            'tahunAktif'          => $tahunAktif,
            'totalSiswaAktif'     => $totalSiswaAktif,
            'totalGuru'           => $totalGuru,
            'totalKelas'          => $totalKelas,
            'totalMapel'          => $totalMapel,
            'siswaBaruTahunIni'   => $siswaBaruTahunIni,
            'chartLabels'         => $chartLabels,
            'chartData'           => $chartData,
            'perJurusan'          => $perJurusan,
            'perGender'           => $perGender,
            'perStatus'           => $perStatus,
            'notifikasiTerbaru'   => $notifikasiTerbaru,
            'hariIni'             => $hariIni,
            'jadwalHariIni'       => $jadwalHariIni,
            'rekapAbsensiHariIni' => $rekapAbsensiHariIni,
            'totalAbsensiHariIni' => $totalAbsensiHariIni,
            'persenHadirHariIni'  => $persenHadirHariIni,
            'pengumumanTerbaru'   => $pengumumanTerbaru,
            'aktivitasTerbaru'    => $aktivitasTerbaru,
        ];

        return view('admin/dashboard/index', $data);
    }
}
