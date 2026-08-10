<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SiswaModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $siswaModel = new SiswaModel();

        $siswa = $siswaModel->select('siswa.*, kelas.nama_kelas')
            ->join('kelas_siswa', 'kelas_siswa.id_siswa = siswa.id_siswa')
            ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas')
            ->where('siswa.user_id', session()->get('id_user'))
            ->first();

        $data['nama']  = session()->get('username');
        $data['siswa'] = $siswa;

        $data['ringkasan'] = [
            'nilai_rata'      => 89,
            'kehadiran'       => 97,
            'tugas_belum'     => 3,
            'pengumuman_baru' => 5,
        ];
        $data['jadwalHariIni'] = [
            ['jam' => '07:00', 'mapel' => 'Matematika'],
            ['jam' => '08:30', 'mapel' => 'Bahasa Indonesia'],
            ['jam' => '10:00', 'mapel' => 'Produktif TKJ'],
        ];
        $data['pengumumanTerbaru'] = [
            'Libur Nasional',
            'Jadwal Ujian',
            'Lomba Sekolah',
        ];

        return view('siswa/dashboard', $data);
    }
}