<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MustChangePassword implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return; // biarkan filter auth yang urus halaman non-login
        }

        if ($session->get('must_change_password')) {
            $allowed = ['auth/gantipassword', 'auth/gantipasswordsubmit', 'auth/logout'];
            $currentPath = trim(current_url(true)->getPath(), '/');

            if (!in_array($currentPath, $allowed, true)) {
                return redirect()->to('/auth/gantipassword')
                    ->with('warning', 'Kamu wajib mengganti password sebelum melanjutkan.');
            }
        }
    }

   public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $session = session();

        // Cegah browser nyimpen cache halaman yang butuh login
        // (mencegah tombol Back nampilin halaman lama setelah logout / must-change-password)
        if ($session->get('logged_in')) {
            $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                      ->setHeader('Pragma', 'no-cache');
        }
    }
}