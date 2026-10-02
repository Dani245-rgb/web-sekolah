<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Mencegah browser menyimpan cache halaman yang butuh login,
 * supaya tombol Back tidak menampilkan halaman lama setelah logout.
 */
class CegahCache implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Tidak ada yang dicek sebelum request diproses
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if (session()->get('logged_in')) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                     ->setHeader('Pragma', 'no-cache');
        }
    }
}