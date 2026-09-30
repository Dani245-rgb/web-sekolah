<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Daftar role dan menu yang bisa diakses, dipakai halaman "Role & Permission".
 *
 * Ini hanya untuk TAMPILAN. Aturan akses yang sebenarnya dijaga di Routes.php
 * (filter roleAuth) dan pengecekan is_superadmin di controller.
 * Kalau menu baru ditambahkan atau aksesnya berubah, perbarui daftar ini juga.
 */
class HakAkses extends BaseConfig
{
    public array $roles = [
        [
            'nama' => 'Admin',
            'desc' => 'Akses ke seluruh menu pengelolaan sekolah dan website.',
            'grup' => [
                'Data Sekolah'  => ['Siswa', 'Import Siswa', 'Laporan Siswa', 'Guru', 'Jurusan', 'PKL'],
                'Website (CMS)' => [
                    'Berita',
                    'Galeri',
                    'Pengumuman',
                    'Prestasi',
                    'Agenda',
                    'Ekstrakurikuler',
                    'Industri Mitra',
                    'PPDB',
                    'Pesan Masuk',
                    'Kalender Akademik',
                    'Profil Sekolah',
                    'Organisasi Sekolah',
                    'Artikel BK',
                    'Pusat Unduhan',
                ],
                'Pengaturan'    => ['Pengaturan Sekolah', 'Notifikasi'],
            ],
        ],
        [
            'nama' => 'Superadmin',
            'desc' => 'Semua akses Admin, ditambah menu yang hanya bisa dibuka superadmin.',
            'grup' => [
                'Khusus Superadmin' => ['User (Admin)', 'Audit Log', 'Backup Database', 'Role & Permission'],
            ],
        ],
        [
            'nama' => 'Guru',
            'desc' => 'Tidak bisa login ke sistem. Data guru dikelola langsung oleh Admin.',
            'grup' => [],
        ],
        [
            'nama' => 'Siswa',
            'desc' => 'Tidak bisa login ke sistem. Data siswa dikelola langsung oleh Admin.',
            'grup' => [],
        ],
    ];
}
