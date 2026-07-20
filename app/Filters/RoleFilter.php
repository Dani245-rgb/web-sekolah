<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Belum login sama sekali
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')
                              ->with('errors', ['login' => 'Silakan login terlebih dahulu.']);
        }

        // 2. Sudah login, tapi role-nya gak sesuai yang diizinkan di route ini
        $userRole = session()->get('role');

        if ($arguments && !in_array($userRole, $arguments, true)) {
            return redirect()->to('/login')
                              ->with('errors', ['login' => 'Anda tidak memiliki akses ke halaman tersebut.']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak dipakai
    }
}