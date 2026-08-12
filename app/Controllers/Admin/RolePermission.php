<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class RolePermission extends BaseController
{
    public function index()
    {
        $data['roles'] = [
            [
                'nama'  => 'Admin',
                'desc'  => 'Akses penuh ke seluruh sistem.',
                'grup'  => [
                    'Data Sekolah'   => ['Tahun Ajaran', 'Siswa', 'Guru', 'Kelas', 'Assign Kelas', 'Mata Pelajaran', 'Jadwal Manager'],
                    'Data Akademik'  => ['Alumni', 'Mutasi', 'Kenaikan Kelas', 'Riwayat Kelas', 'Rekap Absensi', 'Rekap Nilai'],
                    'Website (CMS)'  => ['Berita', 'Galeri', 'Pengumuman', 'Prestasi', 'Agenda', 'Ekstrakurikuler', 'Industri Mitra', 'PPDB', 'Pesan Masuk', 'Kalender Akademik', 'Profil Sekolah', 'Organisasi Sekolah'],
                    'User & Akses'   => ['User (Admin/Guru/Siswa)', 'Audit Log', 'Role & Permission'],
                ],
            ],
            [
                'nama'  => 'Guru',
                'desc'  => 'Akses terbatas ke kelas dan mapel yang diampu.',
                'grup'  => [
                    'Absensi' => ['Input Absensi per Kelas', 'Riwayat Absensi'],
                    'Nilai'   => ['Input Nilai', 'Pengaturan Bobot Nilai', 'Rekap Nilai', 'Import Nilai', 'Riwayat Nilai'],
                ],
            ],
            [
                'nama'  => 'Siswa',
                'desc'  => 'Akses baca saja untuk data pribadi.',
                'grup'  => [
                    'Akademik' => ['Lihat Nilai (read-only)'],
                ],
            ],
        ];

        return view('admin/role_permission/index', $data);
    }
}