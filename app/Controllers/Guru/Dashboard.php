<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\JadwalModel;
use App\Models\AbsensiJadwalModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $userId = session()->get('id_user'); // ⚠️ perlu dipastikan, lihat catatan di bawah

        $guruModel = new GuruModel();
        $guru = $guruModel->where('user_id', $userId)->first();

        if (!$guru) {
            return redirect()->to('/login')->with('errors', ['404' => 'Data guru tidak ditemukan untuk akun ini.']);
        }

        $hariIni = date('l');
        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        $hariIni = $hariMap[$hariIni] ?? $hariIni;
        $tanggalHariIni = date('Y-m-d');

        $jadwalModel = new JadwalModel();
        $jadwalRaw = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->where('jadwal.id_guru', $guru['id_guru'])
            ->where('jadwal.hari', $hariIni)
            ->orderBy('jadwal.jam_mulai', 'ASC')
            ->findAll();

        $absensiJadwalModel = new AbsensiJadwalModel();
        $jadwalHariIni = [];
        $absensiBelum = 0;

        foreach ($jadwalRaw as $j) {
            $sudahAbsen = $absensiJadwalModel->cariByJadwalTanggal($j['id_jadwal'], $tanggalHariIni);
            if (!$sudahAbsen) {
                $absensiBelum++;
            }

            $jadwalHariIni[] = [
                'id_jadwal'   => $j['id_jadwal'],
                'jam'         => substr($j['jam_mulai'], 0, 5) . ' - ' . substr($j['jam_selesai'], 0, 5),
                'kelas'       => $j['nama_kelas'],
                'nama_mapel'  => $j['nama_mapel'],
                'sudah_absen' => $sudahAbsen ? true : false,
            ];
        }

        $data['guru'] = $guru;
        $data['ringkasan'] = [
            'kelas_hari_ini'   => count($jadwalHariIni),
            'nilai_belum'      => 0, // TODO: isi setelah modul Nilai dibuat
            'absensi_belum'    => $absensiBelum,
            'pengumuman_baru'  => 0, // TODO: isi setelah modul Pengumuman dibuat
        ];
        $data['jadwalHariIni'] = $jadwalHariIni;
        $data['pengumumanTerbaru'] = []; // TODO: isi setelah modul Pengumuman dibuat

        return view('guru/dashboard', $data);
    }
}