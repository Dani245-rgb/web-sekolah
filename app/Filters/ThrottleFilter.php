<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $aksi     = $arguments[0] ?? 'default';
        $capacity = isset($arguments[1]) ? (int) $arguments[1] : 10;
        $seconds  = isset($arguments[2]) ? (int) $arguments[2] : 60;

        $throttler = Services::throttler();

        // Bersihkan key dari karakter apapun yang gak aman buat cache
        // (IP IPv6 kayak "::1" mengandung ":" yang dilarang oleh CI4)
        $key = $aksi . '-' . md5($request->getIPAddress());

        if ($throttler->check($key, $capacity, $seconds) === false) {
            return redirect()->back()->withInput()
                ->with('errors', ['throttle' => 'Terlalu banyak percobaan. Silakan coba lagi dalam beberapa saat.']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak dipakai
    }
}