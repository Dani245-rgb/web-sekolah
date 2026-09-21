<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class RolePermission extends BaseController
{
    public function __construct()
    {
        if (!session()->get('is_superadmin')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Halaman tidak ditemukan.');
        }
    }

    public function index()
    {
        $data['roles'] = [
            [
                'nama'  => 'Admin',
                'desc'  => 'Akses penuh ke seluruh sistem.',
                'grup'  => [
                    'Data Sekolah'   => ['Siswa', 'Guru'],
                    'Website (CMS)'  => ['Berita', 'Galeri', 'Pengumuman', 'Prestasi', 'Agenda', 'Ekstrakurikuler', 'Industri Mitra', 'PPDB', 'Pesan Masuk', 'Kalender Akademik', 'Profil Sekolah', 'Organisasi Sekolah'],
                    'User & Akses'   => ['User (Admin)', 'Audit Log', 'Role & Permission'],
                ],
            ],
            [
                'nama'  => 'Guru',
                'desc'  => 'Tidak bisa login ke sistem. Data guru dikelola langsung oleh Admin.',
                'grup'  => [],
            ],
            [
                'nama'  => 'Siswa',
                'desc'  => 'Tidak bisa login ke sistem. Data siswa dikelola langsung oleh Admin.',
                'grup'  => [],
            ],
        ];

        return view('admin/role_permission/index', $data);
    }
}
