<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth\Login::index');
$routes->post('login', 'Auth\Login::authenticate', ['filter' => 'throttle:login,5,60']);

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
    $routes->post('tahun-ajaran/delete/(:num)', 'Admin\TahunAjaran::delete/$1');

    // Data Sekolah - Guru
    $routes->get('guru', 'Admin\Guru::index');
    $routes->get('guru/create', 'Admin\Guru::create');
    $routes->post('guru/store', 'Admin\Guru::store');
    $routes->get('guru/edit/(:num)', 'Admin\Guru::edit/$1');
    $routes->post('guru/update/(:num)', 'Admin\Guru::update/$1');
    $routes->post('guru/delete/(:num)', 'Admin\Guru::delete/$1');

    // Data Sekolah - Kelas
    $routes->get('kelas', 'Admin\Kelas::index');
    $routes->get('kelas/create', 'Admin\Kelas::create');
    $routes->post('kelas/store', 'Admin\Kelas::store');
    $routes->get('kelas/edit/(:num)', 'Admin\Kelas::edit/$1');
    $routes->post('kelas/update/(:num)', 'Admin\Kelas::update/$1');
    $routes->post('kelas/delete/(:num)', 'Admin\Kelas::delete/$1');
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
    $routes->post('mapel/delete/(:num)', 'Admin\Mapel::delete/$1');

    // Jurusan
    $routes->get('jurusan', 'Admin\Jurusan::index');
    $routes->get('jurusan/create', 'Admin\Jurusan::create');
    $routes->post('jurusan/store', 'Admin\Jurusan::store');
    $routes->get('jurusan/edit/(:num)', 'Admin\Jurusan::edit/$1');
    $routes->post('jurusan/update/(:num)', 'Admin\Jurusan::update/$1');
    $routes->post('jurusan/delete/(:num)', 'Admin\Jurusan::delete/$1');

    // Data Sekolah - Siswa
    $routes->get('siswa', 'Admin\Siswa::index');
    $routes->get('siswa/create', 'Admin\Siswa::create');
    $routes->post('siswa/store', 'Admin\Siswa::store');
    $routes->get('siswa/edit/(:num)', 'Admin\Siswa::edit/$1');
    $routes->post('siswa/update/(:num)', 'Admin\Siswa::update/$1');
    $routes->post('siswa/delete/(:num)', 'Admin\Siswa::delete/$1');
    $routes->get('siswa/trash', 'Admin\Siswa::trash');
    $routes->post('siswa/restore/(:num)', 'Admin\Siswa::restore/$1');
    $routes->post('siswa/force-delete/(:num)', 'Admin\Siswa::forceDelete/$1');

    // Import Siswa Massal
    $routes->get('siswa/import', 'Admin\ImportSiswa::index');
    $routes->get('siswa/import/template', 'Admin\ImportSiswa::template');
    $routes->post('siswa/import/upload', 'Admin\ImportSiswa::upload');
    $routes->post('siswa/import/proses/(:num)', 'Admin\ImportSiswa::proses/$1');
    $routes->get('siswa/import/status/(:num)', 'Admin\ImportSiswa::status/$1');

    // Audit Log
    $routes->get('audit-log', 'Admin\AuditLog::index');
    $routes->get('audit-log/export/pdf', 'Admin\AuditLog::exportPdf');
    $routes->get('audit-log/export/excel', 'Admin\AuditLog::exportExcel');

    // Assign Kelas Siswa
    $routes->get('assign-kelas', 'Admin\AssignKelas::index');
    $routes->get('assign-kelas/form', 'Admin\AssignKelas::form');
    $routes->get('assign-kelas/siswa-belum-assign', 'Admin\AssignKelas::siswaBelumAssign');
    $routes->post('assign-kelas/proses', 'Admin\AssignKelas::proses');
    $routes->post('assign-kelas/batal/(:num)', 'Admin\AssignKelas::batal/$1');

    // Jadwal Pelajaran
    $routes->get('jadwal', 'Admin\Jadwal::index');
    $routes->get('jadwal/create', 'Admin\Jadwal::create');
    $routes->post('jadwal/store', 'Admin\Jadwal::store');
    $routes->get('jadwal/edit/(:num)', 'Admin\Jadwal::edit/$1');
    $routes->post('jadwal/update/(:num)', 'Admin\Jadwal::update/$1');
    $routes->post('jadwal/delete/(:num)', 'Admin\Jadwal::delete/$1');
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
    $routes->post('alumni/batal/(:num)', 'Admin\Alumni::batal/$1');

    // Mutasi siswa
    $routes->get('mutasi', 'Admin\Mutasi::index');
    $routes->get('mutasi/form', 'Admin\Mutasi::form');
    $routes->get('mutasi/siswa-per-kelas/(:num)', 'Admin\Mutasi::siswaPerKelas/$1');
    $routes->post('mutasi/proses', 'Admin\Mutasi::proses');
    $routes->post('mutasi/batal/(:num)', 'Admin\Mutasi::batal/$1');

    // Kenaikan Kelas
    $routes->get('kenaikan-kelas', 'Admin\KenaikanKelas::index');
    $routes->get('kenaikan-kelas/form', 'Admin\KenaikanKelas::form');
    $routes->get('kenaikan-kelas/siswa-by-kelas/(:num)/(:num)', 'Admin\KenaikanKelas::siswaByKelas/$1/$2');
    $routes->post('kenaikan-kelas/proses', 'Admin\KenaikanKelas::proses');

    // Berita (CMS)
    $routes->get('berita', 'Admin\Berita::index');
    $routes->get('berita/create', 'Admin\Berita::create');
    $routes->post('berita/store', 'Admin\Berita::store');
    $routes->get('berita/edit/(:num)', 'Admin\Berita::edit/$1');
    $routes->post('berita/update/(:num)', 'Admin\Berita::update/$1');
    $routes->post('berita/delete/(:num)', 'Admin\Berita::delete/$1');

    // Galeri
    $routes->get('galeri', 'Admin\Galeri::index');
    $routes->get('galeri/create', 'Admin\Galeri::create');
    $routes->post('galeri/store', 'Admin\Galeri::store');
    $routes->get('galeri/edit/(:num)', 'Admin\Galeri::edit/$1');
    $routes->post('galeri/update/(:num)', 'Admin\Galeri::update/$1');
    $routes->post('galeri/delete/(:num)', 'Admin\Galeri::delete/$1');

    // Pengumuman
    $routes->get('pengumuman', 'Admin\Pengumuman::index');
    $routes->get('pengumuman/create', 'Admin\Pengumuman::create');
    $routes->post('pengumuman/store', 'Admin\Pengumuman::store');
    $routes->get('pengumuman/edit/(:num)', 'Admin\Pengumuman::edit/$1');
    $routes->post('pengumuman/update/(:num)', 'Admin\Pengumuman::update/$1');
    $routes->post('pengumuman/delete/(:num)', 'Admin\Pengumuman::delete/$1');

    // Prestasi
    $routes->get('prestasi', 'Admin\Prestasi::index');
    $routes->get('prestasi/create', 'Admin\Prestasi::create');
    $routes->post('prestasi/store', 'Admin\Prestasi::store');
    $routes->get('prestasi/edit/(:num)', 'Admin\Prestasi::edit/$1');
    $routes->post('prestasi/update/(:num)', 'Admin\Prestasi::update/$1');
    $routes->post('prestasi/delete/(:num)', 'Admin\Prestasi::delete/$1');

    // Agenda
    $routes->get('agenda', 'Admin\Agenda::index');
    $routes->get('agenda/create', 'Admin\Agenda::create');
    $routes->post('agenda/store', 'Admin\Agenda::store');
    $routes->get('agenda/edit/(:num)', 'Admin\Agenda::edit/$1');
    $routes->post('agenda/update/(:num)', 'Admin\Agenda::update/$1');
    $routes->post('agenda/delete/(:num)', 'Admin\Agenda::delete/$1');

    // Ekstrakurikuler
    $routes->get('ekstrakurikuler', 'Admin\Ekstrakurikuler::index');
    $routes->get('ekstrakurikuler/create', 'Admin\Ekstrakurikuler::create');
    $routes->post('ekstrakurikuler/store', 'Admin\Ekstrakurikuler::store');
    $routes->get('ekstrakurikuler/edit/(:num)', 'Admin\Ekstrakurikuler::edit/$1');
    $routes->post('ekstrakurikuler/update/(:num)', 'Admin\Ekstrakurikuler::update/$1');
    $routes->post('ekstrakurikuler/delete/(:num)', 'Admin\Ekstrakurikuler::delete/$1');

    // Partner
    $routes->get('partner', 'Admin\Partner::index');
    $routes->get('partner/create', 'Admin\Partner::create');
    $routes->post('partner/store', 'Admin\Partner::store');
    $routes->get('partner/edit/(:num)', 'Admin\Partner::edit/$1');
    $routes->post('partner/update/(:num)', 'Admin\Partner::update/$1');
    $routes->post('partner/delete/(:num)', 'Admin\Partner::delete/$1');

    // PPDB
    $routes->get('ppdb', 'Admin\PpdbAdmin::index');
    $routes->get('ppdb/detail/(:num)', 'Admin\PpdbAdmin::detail/$1');
    $routes->post('ppdb/update-status/(:num)', 'Admin\PpdbAdmin::updateStatus/$1');
    $routes->post('ppdb/delete/(:num)', 'Admin\PpdbAdmin::delete/$1');
    $routes->get('ppdb/pengaturan', 'Admin\PpdbAdmin::pengaturan');
    $routes->post('ppdb/pengaturan/update', 'Admin\PpdbAdmin::updatePengaturan');

    // Kontak
    $routes->get('kontak', 'Admin\KontakAdmin::index');
    $routes->get('kontak/detail/(:num)', 'Admin\KontakAdmin::detail/$1');
    $routes->post('kontak/delete/(:num)', 'Admin\KontakAdmin::delete/$1');

    // Kalender Akademik
    $routes->get('kalender-akademik', 'Admin\KalenderAkademik::index');
    $routes->get('kalender-akademik/create', 'Admin\KalenderAkademik::create');
    $routes->post('kalender-akademik/store', 'Admin\KalenderAkademik::store');
    $routes->get('kalender-akademik/edit/(:num)', 'Admin\KalenderAkademik::edit/$1');
    $routes->post('kalender-akademik/update/(:num)', 'Admin\KalenderAkademik::update/$1');
    $routes->post('kalender-akademik/delete/(:num)', 'Admin\KalenderAkademik::delete/$1');

    // Profil Sekolah (konten statis)
    $routes->get('profil-sekolah/sejarah', 'Admin\ProfilSekolah::sejarah');
    $routes->get('profil-sekolah/visi-misi', 'Admin\ProfilSekolah::visiMisi');
    $routes->post('profil-sekolah/update-teks/(:segment)', 'Admin\ProfilSekolah::updateTeks/$1');
    $routes->get('profil-sekolah/kepala-sekolah', 'Admin\ProfilSekolah::kepalaSekolah');
    $routes->post('profil-sekolah/update-kepala-sekolah', 'Admin\ProfilSekolah::updateKepalaSekolah');

    // Organisasi
    $routes->get('organisasi', 'Admin\Organisasi::index');
    $routes->get('organisasi/create', 'Admin\Organisasi::create');
    $routes->post('organisasi/store', 'Admin\Organisasi::store');
    $routes->get('organisasi/edit/(:num)', 'Admin\Organisasi::edit/$1');
    $routes->post('organisasi/update/(:num)', 'Admin\Organisasi::update/$1');
    $routes->post('organisasi/delete/(:num)', 'Admin\Organisasi::delete/$1');

    // Anggota Organisasi (nested)
    $routes->get('organisasi/(:num)/anggota', 'Admin\AnggotaOrganisasi::index/$1');
    $routes->get('organisasi/(:num)/anggota/create', 'Admin\AnggotaOrganisasi::create/$1');
    $routes->post('organisasi/(:num)/anggota/store', 'Admin\AnggotaOrganisasi::store/$1');
    $routes->get('organisasi/(:num)/anggota/edit/(:num)', 'Admin\AnggotaOrganisasi::edit/$1/$2');
    $routes->post('organisasi/(:num)/anggota/update/(:num)', 'Admin\AnggotaOrganisasi::update/$1/$2');
    $routes->post('organisasi/(:num)/anggota/delete/(:num)', 'Admin\AnggotaOrganisasi::delete/$1/$2');

    $routes->get('user', 'Admin\User::index');
    $routes->get('user/admin', 'Admin\User::index/admin');
    $routes->get('role-permission', 'Admin\RolePermission::index');

    // Backup Database
    $routes->get('backup-database', 'Admin\BackupDatabase::index');
    $routes->post('backup-database/create', 'Admin\BackupDatabase::create');
    $routes->get('backup-database/download/(:segment)', 'Admin\BackupDatabase::download/$1');
    $routes->post('backup-database/delete/(:segment)', 'Admin\BackupDatabase::delete/$1');

    // Pengaturan
    $routes->get('pengaturan', 'Admin\Pengaturan::index');
    $routes->post('pengaturan/update', 'Admin\Pengaturan::update');

    // Laporan Siswa
    $routes->get('laporan-siswa', 'Admin\LaporanSiswa::index');
    $routes->get('laporan-siswa/export/excel', 'Admin\LaporanSiswa::exportExcel');

    // Laporan Akademik
    $routes->get('laporan-akademik', 'Admin\LaporanAkademik::index');

    // Notifikasi
    $routes->get('notifikasi', 'Admin\Notifikasi::index');
    $routes->get('notifikasi/poll', 'Admin\Notifikasi::poll', ['filter' => 'throttle:notif-poll,30,60']);
    $routes->post('notifikasi/baca/(:num)', 'Admin\Notifikasi::baca/$1');
    $routes->post('notifikasi/baca-semua', 'Admin\Notifikasi::bacaSemua');

    $routes->get('user/create-admin', 'Admin\User::createAdmin');
    $routes->post('user/store-admin', 'Admin\User::storeAdmin');
    $routes->post('user/unlock/(:num)', 'Admin\User::unlock/$1');
    $routes->post('user/reset-password/(:num)', 'Admin\User::resetPassword/$1');

    // BK - Artikel
    $routes->get('bk-artikel', 'Admin\BkArtikel::index');
    $routes->get('bk-artikel/create', 'Admin\BkArtikel::create');
    $routes->post('bk-artikel/store', 'Admin\BkArtikel::store');
    $routes->get('bk-artikel/edit/(:num)', 'Admin\BkArtikel::edit/$1');
    $routes->post('bk-artikel/update/(:num)', 'Admin\BkArtikel::update/$1');
    $routes->post('bk-artikel/delete/(:num)', 'Admin\BkArtikel::delete/$1');

    // Unduhan (Pusat Download)
    $routes->get('unduhan', 'Admin\Unduhan::index');
    $routes->get('unduhan/create', 'Admin\Unduhan::create');
    $routes->post('unduhan/store', 'Admin\Unduhan::store');
    $routes->get('unduhan/edit/(:num)', 'Admin\Unduhan::edit/$1');
    $routes->post('unduhan/update/(:num)', 'Admin\Unduhan::update/$1');
    $routes->post('unduhan/delete/(:num)', 'Admin\Unduhan::delete/$1');

    // PKL
    $routes->get('pkl', 'Admin\Pkl::index');
    $routes->get('pkl/create', 'Admin\Pkl::create');
    $routes->post('pkl/store', 'Admin\Pkl::store');
    $routes->get('pkl/edit/(:num)', 'Admin\Pkl::edit/$1');
    $routes->post('pkl/update/(:num)', 'Admin\Pkl::update/$1');
    $routes->post('pkl/delete/(:num)', 'Admin\Pkl::delete/$1');
});

$routes->get('profil/gantipassword', 'Profil::gantipassword', ['filter' => 'auth']);
$routes->post('profil/gantipasswordsubmit', 'Profil::gantipasswordsubmit', ['filter' => 'auth']);

$routes->get('logout', 'Auth\Logout::index', ['filter' => 'auth']);

// Berita
$routes->get('berita', 'Berita::index');
$routes->get('berita/(:segment)', 'Berita::detail/$1');

// pengumuman
$routes->get('pengumuman', 'Pengumuman::index');
$routes->get('pengumuman/(:segment)', 'Pengumuman::detail/$1');

// prestasi
$routes->get('prestasi', 'Prestasi::index');

// Agenda
$routes->get('agenda', 'Agenda::index');

// Galeri
$routes->get('galeri', 'Galeri::index');

// Ekstrakulikuler
$routes->get('ekstrakurikuler', 'Ekstrakurikuler::index');

// Parnet industri
$routes->get('partner', 'Partner::index');
$routes->get('partner/detail/(:segment)', 'Partner::detail/$1');

// Ppdb
$routes->get('ppdb', 'Ppdb::index');
$routes->post('ppdb/daftar', 'Ppdb::daftar', ['filter' => 'throttle:ppdb,3,120']);

// Kontak
$routes->get('kontak', 'Kontak::index');
$routes->post('kontak/kirim', 'Kontak::kirim', ['filter' => 'throttle:kontak,3,60']);

// Akademik - Guru & Staff
$routes->get('akademik/guru', 'GuruPublik::index');

// Akademik - Jadwal
$routes->get('akademik/jadwal', 'JadwalPublik::index');

// Akademik - Kalender Akademik
$routes->get('akademik/kalender', 'KalenderAkademikPublik::index');

// Akademik - Jurusan
$routes->get('akademik/jurusan', 'JurusanPublik::index');
$routes->get('akademik/jurusan/(:segment)', 'JurusanPublik::detail/$1');

// Profil
$routes->get('profil/sejarah', 'ProfilPublik::sejarah');
$routes->get('profil/visi-misi', 'ProfilPublik::visiMisi');
$routes->get('profil/struktur', 'ProfilPublik::struktur');
$routes->get('profil/kepala-sekolah', 'ProfilPublik::kepalaSekolah');

// BK - Layanan Bimbingan Konseling
$routes->get('bk/kesehatan-mental', 'BkArtikelPublik::kesehatanMental');
$routes->get('bk/karier', 'BkArtikelPublik::karier');
$routes->get('bk/tes-minat', 'BkArtikelPublik::tesMinat');
$routes->get('bk/artikel/(:segment)', 'BkArtikelPublik::detail/$1');

// Unduhan (Pusat Download)
$routes->get('unduhan', 'UnduhanPublik::index');
$routes->get('unduhan/download/(:num)', 'UnduhanPublik::download/$1');