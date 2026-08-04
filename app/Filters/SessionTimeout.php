<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionTimeout implements FilterInterface
{
    private const IDLE_LIMIT = 1800; // 30 menit, dalam detik

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return;
        }

        $lastActivity = $session->get('last_activity');

        if ($lastActivity !== null && (time() - $lastActivity) > self::IDLE_LIMIT) {
            $session->destroy();
            return redirect()->to('/login')
                ->with('errors', ['login' => 'Sesi kamu berakhir karena tidak aktif. Silakan login lagi.']);
        }

        $session->set('last_activity', time());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}