<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth\Login::index');

$routes->post('login', 'Auth\Login::authenticate');

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

    // Data Sekolah - Mata Pelajaran
    $routes->get('mapel', 'Admin\Mapel::index');
    $routes->get('mapel/create', 'Admin\Mapel::create');
    $routes->post('mapel/store', 'Admin\Mapel::store');
    $routes->get('mapel/edit/(:num)', 'Admin\Mapel::edit/$1');
    $routes->post('mapel/update/(:num)', 'Admin\Mapel::update/$1');
    $routes->get('mapel/delete/(:num)', 'Admin\Mapel::delete/$1');
});

$routes->group('guru', ['filter' => 'roleAuth:Guru'], function ($routes) {
    $routes->get('dashboard', 'Guru\Dashboard::index');
});

$routes->group('siswa', ['filter' => 'roleAuth:Siswa'], function ($routes) {
    $routes->get('dashboard', 'Siswa\Dashboard::index');
});

$routes->get('logout', 'Auth\Logout::index');