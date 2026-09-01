<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\JadwalModel;
use App\Models\KelasSiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\SemesterModel;
use App\Models\PengaturanNilaiModel;
use App\Models\KomponenNilaiModel;
use App\Models\NilaiSiswaModel;
use App\Models\AbsensiJadwalModel;
use App\Models\AbsensiDetailModel;

class DataKelas extends BaseController
{
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

    protected function redirectGuruTidakDitemukan()
    {
        return redirect()->to('/logout')
            ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
    }

    public function index()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $jadwalModel = new JadwalModel();
        $daftar = $jadwalModel->select('jadwal.id_kelas, jadwal.id_mapel, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->where('jadwal.id_guru', $guru['id_guru'])
            ->groupBy('jadwal.id_kelas, jadwal.id_mapel')
            ->orderBy('kelas.nama_kelas', 'ASC')
            ->findAll();

        return view('guru/data_kelas/index', [
            'daftar' => $daftar,
        ]);
    }

    public function siswa($idKelas, $idMapel)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $jadwalModel = new JadwalModel();
        $jadwalSaya = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->where('jadwal.id_guru', $guru['id_guru'])
            ->where('jadwal.id_kelas', $idKelas)
            ->where('jadwal.id_mapel', $idMapel)
            ->findAll();

        if (empty($jadwalSaya)) {
            return redirect()->to('/guru/kelas')->with('errors', ['403' => 'Anda tidak mengajar kelas & mapel ini.']);
        }

        $kelasInfo = $jadwalSaya[0];
        $idJadwalList = array_column($jadwalSaya, 'id_jadwal');

        $semesterModel = new SemesterModel();
        $semesterAktif = $semesterModel->getActive();

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        $daftarSiswa = [];
        if ($tahunAktif) {
            $kelasSiswaModel = new KelasSiswaModel();
            $daftarSiswa = $kelasSiswaModel->getSiswaByKelas($idKelas, $tahunAktif['id_tahun_ajaran']);
        }

        // Siapkan komponen nilai (kalau guru sudah atur pengaturan nilai utk kelas+mapel ini)
        $komponenList = [];
        if ($semesterAktif) {
            $pengaturanModel = new PengaturanNilaiModel();
            $pengaturan = $pengaturanModel->getPengaturan(
                (int) $guru['id_guru'],
                (int) $idKelas,
                (int) $idMapel,
                (int) $semesterAktif['id_semester']
            );

            if ($pengaturan) {
                $komponenModel = new KomponenNilaiModel();
                $komponenList = $komponenModel->where('id_pengaturan', $pengaturan['id_pengaturan'])->findAll();
            }
        }

        $nilaiSiswaModel = new NilaiSiswaModel();
        $absensiDetailModel = new AbsensiDetailModel();

        foreach ($daftarSiswa as &$s) {
            // Rata-rata nilai (weighted by bobot, hanya komponen yang sudah diisi)
            $totalBobot = 0;
            $totalNilaiBobot = 0;
            foreach ($komponenList as $k) {
                $nilai = $nilaiSiswaModel
                    ->where('id_siswa', $s['id_siswa'])
                    ->where('id_komponen', $k['id_komponen'])
                    ->first();

                if ($nilai) {
                    $totalNilaiBobot += $nilai['nilai'] * $k['bobot'];
                    $totalBobot += $k['bobot'];
                }
            }
            $s['rata_nilai'] = $totalBobot > 0 ? round($totalNilaiBobot / $totalBobot, 2) : null;

            // Persentase kehadiran (dari semua sesi absensi_jadwal guru ini utk kelas+mapel ini)
            $totalPertemuan = $absensiDetailModel
                ->join('absensi_jadwal', 'absensi_jadwal.id_absensi_jadwal = absensi_detail.id_absensi_jadwal')
                ->whereIn('absensi_jadwal.id_jadwal', $idJadwalList)
                ->where('absensi_detail.id_siswa', $s['id_siswa'])
                ->countAllResults();

            $totalHadir = $absensiDetailModel
                ->join('absensi_jadwal', 'absensi_jadwal.id_absensi_jadwal = absensi_detail.id_absensi_jadwal')
                ->whereIn('absensi_jadwal.id_jadwal', $idJadwalList)
                ->where('absensi_detail.id_siswa', $s['id_siswa'])
                ->where('absensi_detail.status', 'Hadir')
                ->countAllResults();

            $s['persen_hadir'] = $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100, 1) : null;
        }
        unset($s);

        return view('guru/data_kelas/siswa', [
            'kelasInfo'   => $kelasInfo,
            'daftarSiswa' => $daftarSiswa,
        ]);
    }
}
