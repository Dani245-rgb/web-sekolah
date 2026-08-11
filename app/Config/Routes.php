<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth\Login::index');
$routes->post('login', 'Auth\Login::authenticate');

// Wajib ganti password - berlaku SEMUA role, makanya di luar grup admin/guru/siswa
$routes->get('auth/gantipassword', 'Auth\Login::gantipassword');
$routes->post('auth/gantipasswordsubmit', 'Auth\Login::gantipasswordsubmit');

$routes->group('admin', ['filter' => 'roleAuth:Admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // Tahun Ajaran (fondasi)
    $routes->get('tahun-ajaran', 'Admin\TahunAjaran::index');
    $routes->get('tahun-ajaran/create', 'Admin\TahunAjaran::create');
    $routes->post('tahun-ajaran/store', 'Admin\TahunAjaran::store');
    $routes->get('tahun-ajaran/edit/(:num)', 'Admin\TahunAjaran::edit/$1');
    $routes->post('tahun-ajaran/update/(:num)', 'Admin\TahunAjaran::update/$1');
    $routes->get('tahun-ajaran/delete/(:num)', 'Admin\TahunAjaran::delete/$1');

    // Data Sekolah - Guru
    $routes->get('guru', 'Admin\Guru::index');
    $routes->get('guru/create', 'Admin\Guru::create');
    $routes->post('guru/store', 'Admin\Guru::store');
    $routes->get('guru/edit/(:num)', 'Admin\Guru::edit/$1');
    $routes->post('guru/update/(:num)', 'Admin\Guru::update/$1');
    $routes->get('guru/delete/(:num)', 'Admin\Guru::delete/$1');

    // Data Sekolah - Kelas
    $routes->get('kelas', 'Admin\Kelas::index');
    $routes->get('kelas/create', 'Admin\Kelas::create');
    $routes->post('kelas/store', 'Admin\Kelas::store');
    $routes->get('kelas/edit/(:num)', 'Admin\Kelas::edit/$1');
    $routes->post('kelas/update/(:num)', 'Admin\Kelas::update/$1');
    $routes->get('kelas/delete/(:num)', 'Admin\Kelas::delete/$1');
    $routes->get('kelas/siswa/(:num)', 'Admin\Kelas::siswa/$1');

    // Riwayat Kelas
    $routes->get('riwayat-kelas', 'Admin\RiwayatKelas::index');
    $routes->get('riwayat-kelas/detail/(:num)', 'Admin\RiwayatKelas::detail/$1');

    // Data Sekolah - Mata Pelajaran
    $routes->get('mapel', 'Admin\Mapel::index');
    $routes->get('mapel/create', 'Admin\Mapel::create');
    $routes->post('mapel/store', 'Admin\Mapel::store');
    $routes->get('mapel/edit/(:num)', 'Admin\Mapel::edit/$1');
    $routes->post('mapel/update/(:num)', 'Admin\Mapel::update/$1');
    $routes->get('mapel/delete/(:num)', 'Admin\Mapel::delete/$1');

    // Data Sekolah - Siswa
    $routes->get('siswa', 'Admin\Siswa::index');
    $routes->get('siswa/create', 'Admin\Siswa::create');
    $routes->post('siswa/store', 'Admin\Siswa::store');
    $routes->get('siswa/edit/(:num)', 'Admin\Siswa::edit/$1');
    $routes->post('siswa/update/(:num)', 'Admin\Siswa::update/$1');
    $routes->get('siswa/delete/(:num)', 'Admin\Siswa::delete/$1');

    // Import Siswa Massal
    $routes->get('siswa/import', 'Admin\ImportSiswa::index');
    $routes->get('siswa/import/template', 'Admin\ImportSiswa::template');
    $routes->post('siswa/import/upload', 'Admin\ImportSiswa::upload');
    $routes->post('siswa/import/proses/(:num)', 'Admin\ImportSiswa::proses/$1');
    $routes->get('siswa/import/status/(:num)', 'Admin\ImportSiswa::status/$1');

    // Reset Password Siswa (oleh Admin)
    $routes->get('siswa/resetpassword/(:num)', 'Admin\Siswa::resetPassword/$1');

    // Audit Log
    $routes->get('audit-log', 'Admin\AuditLog::index');
    $routes->get('audit-log/export/pdf', 'Admin\AuditLog::exportPdf');
    $routes->get('audit-log/export/excel', 'Admin\AuditLog::exportExcel');

    // Assign Kelas Siswa
    $routes->get('assign-kelas', 'Admin\AssignKelas::index');
    $routes->get('assign-kelas/form', 'Admin\AssignKelas::form');
    $routes->get('assign-kelas/siswa-belum-assign', 'Admin\AssignKelas::siswaBelumAssign');
    $routes->post('assign-kelas/proses', 'Admin\AssignKelas::proses');
    $routes->get('assign-kelas/batal/(:num)', 'Admin\AssignKelas::batal/$1');

    // Jadwal Pelajaran
    $routes->get('jadwal', 'Admin\Jadwal::index');
    $routes->get('jadwal/create', 'Admin\Jadwal::create');
    $routes->post('jadwal/store', 'Admin\Jadwal::store');
    $routes->get('jadwal/edit/(:num)', 'Admin\Jadwal::edit/$1');
    $routes->post('jadwal/update/(:num)', 'Admin\Jadwal::update/$1');
    $routes->get('jadwal/delete/(:num)', 'Admin\Jadwal::delete/$1');
    $routes->get('jadwal/template', 'Admin\Jadwal::template');
    $routes->get('jadwal/import', 'Admin\Jadwal::importForm');
    $routes->post('jadwal/import/preview', 'Admin\Jadwal::importPreview');
    $routes->post('jadwal/import/confirm', 'Admin\Jadwal::importConfirm');
    $routes->get('jadwal/export', 'Admin\Jadwal::exportExcel');
    $routes->get('jadwal/export/pdf', 'Admin\Jadwal::exportPdf');

    // Assign massal Jurusan ke Kelas
    $routes->get('kelas/assign-jurusan', 'Admin\Kelas::assignJurusanForm');
    $routes->post('kelas/assign-jurusan', 'Admin\Kelas::assignJurusanProses');

    // Alumni siswa
    $routes->get('alumni', 'Admin\Alumni::index');
    $routes->get('alumni/form', 'Admin\Alumni::form');
    $routes->get('alumni/siswa-per-kelas/(:num)', 'Admin\Alumni::siswaPerKelas/$1');
    $routes->post('alumni/proses', 'Admin\Alumni::proses');
    $routes->get('alumni/batal/(:num)', 'Admin\Alumni::batal/$1');

    // Mutasi siswa
    $routes->get('mutasi', 'Admin\Mutasi::index');
    $routes->get('mutasi/form', 'Admin\Mutasi::form');
    $routes->get('mutasi/siswa-per-kelas/(:num)', 'Admin\Mutasi::siswaPerKelas/$1');
    $routes->post('mutasi/proses', 'Admin\Mutasi::proses');
    $routes->get('mutasi/batal/(:num)', 'Admin\Mutasi::batal/$1');

    // Kenaikan Kelas
    $routes->get('kenaikan-kelas', 'Admin\KenaikanKelas::index');
    $routes->get('kenaikan-kelas/form', 'Admin\KenaikanKelas::form');
    $routes->get('kenaikan-kelas/siswa-by-kelas/(:num)/(:num)', 'Admin\KenaikanKelas::siswaByKelas/$1/$2');
    $routes->post('kenaikan-kelas/proses', 'Admin\KenaikanKelas::proses');

    // Rekap Kelas
    $routes->get('rekap-absensi', 'Admin\RekapAbsensi::index');
    $routes->get('rekap-absensi/export/pdf', 'Admin\RekapAbsensi::exportPdfKelas');
    $routes->get('rekap-absensi/export/excel', 'Admin\RekapAbsensi::exportExcelKelas');
    $routes->get('rekap-absensi/siswa', 'Admin\RekapAbsensi::siswa');
    $routes->get('rekap-absensi/siswa/detail/(:num)', 'Admin\RekapAbsensi::siswaDetail/$1');
    $routes->get('rekap-absensi/siswa/detail/(:num)/export/pdf', 'Admin\RekapAbsensi::exportPdfSiswa/$1');
    $routes->get('rekap-absensi/siswa/detail/(:num)/export/excel', 'Admin\RekapAbsensi::exportExcelSiswa/$1');


    // Berita (CMS)
    $routes->get('berita', 'Admin\Berita::index');
    $routes->get('berita/create', 'Admin\Berita::create');
    $routes->post('berita/store', 'Admin\Berita::store');
    $routes->get('berita/edit/(:num)', 'Admin\Berita::edit/$1');
    $routes->post('berita/update/(:num)', 'Admin\Berita::update/$1');
    $routes->get('berita/delete/(:num)', 'Admin\Berita::delete/$1');

    // Rekap Nilai
    $routes->get('nilai/rekap', 'Admin\RekapNilai::index');
    $routes->get('nilai/rekap/(:num)', 'Admin\RekapNilai::detail/$1');
    $routes->get('nilai/rekap/(:num)/export/pdf', 'Admin\RekapNilai::exportPdf/$1');
    $routes->get('nilai/rekap/(:num)/export/excel', 'Admin\RekapNilai::exportExcel/$1');
});

$routes->group('guru', ['filter' => 'roleAuth:Guru'], function ($routes) {
    $routes->get('dashboard', 'Guru\Dashboard::index');
    $routes->get('absensi/form/(:num)', 'Guru\Absensi::form/$1');
    $routes->post('absensi/simpan', 'Guru\Absensi::simpan');
    $routes->get('absensi/riwayat/(:num)', 'Guru\Absensi::riwayat/$1');

    // Nilai
    $routes->get('nilai/form/(:num)', 'Guru\Nilai::form/$1');
    $routes->get('nilai/form/(:num)/pengaturan', 'Guru\Nilai::pengaturanManual/$1');
    $routes->post('nilai/form/(:num)/pengaturan', 'Guru\Nilai::simpanPengaturan/$1');
    $routes->post('nilai/form/(:num)/simpan', 'Guru\Nilai::simpan/$1');
    $routes->get('nilai/rekap/(:num)', 'Guru\Nilai::rekap/$1');
    $routes->get('nilai/rekap/(:num)/export/pdf', 'Guru\Nilai::exportPdf/$1');

    $routes->get('nilai/form/(:num)/riwayat', 'Guru\Nilai::riwayat/$1');

    // Import Nilai
    $routes->get('nilai/import/template/(:num)', 'Guru\ImportNilai::template/$1');
    $routes->post('nilai/import/preview', 'Guru\ImportNilai::preview');
    $routes->post('nilai/import/konfirmasi', 'Guru\ImportNilai::konfirmasi');
    $routes->post('nilai/import/rollback/(:num)', 'Guru\ImportNilai::rollback/$1');
});

$routes->group('siswa', ['filter' => 'roleAuth:Siswa'], function ($routes) {
    $routes->get('dashboard', 'Siswa\Dashboard::index');

    // Nilai (read-only)
    $routes->get('nilai', 'Siswa\Nilai::index');
});

$routes->get('profil/gantipassword', 'Profil::gantipassword', ['filter' => 'auth']);
$routes->post('profil/gantipasswordsubmit', 'Profil::gantipasswordsubmit', ['filter' => 'auth']);

$routes->get('logout', 'Auth\Logout::index', ['filter' => 'auth']);

$routes->get('berita', 'Berita::index');
$routes->get('berita/(:segment)', 'Berita::detail/$1');
