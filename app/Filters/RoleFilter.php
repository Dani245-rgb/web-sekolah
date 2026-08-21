<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Pakai ulang AuthFilter buat cek login dasar
        $authFilter = new AuthFilter();
        $authResult = $authFilter->before($request, $arguments);

        // Kalau AuthFilter sudah nge-redirect (berarti belum login), langsung stop di sini
        if ($authResult !== null) {
            return $authResult;
        }

        // Baru cek role-nya
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