<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('login', 'Auth\Login::index');

$routes->post('login', 'Auth\Login::authenticate');

$routes->group('admin', ['filter' => 'roleAuth:Admin'], function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
});

$routes->group('guru', ['filter' => 'roleAuth:Guru'], function ($routes) {
    $routes->get('dashboard', 'Guru\Dashboard::index');
});

$routes->group('siswa', ['filter' => 'roleAuth:Siswa'], function ($routes) {
    $routes->get('dashboard', 'Siswa\Dashboard::index');
});

$routes->get('logout', 'Auth\Logout::index');