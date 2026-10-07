<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Services\AuditLogService;

class Logout extends BaseController
{
    protected AuditLogService $auditLogService;

    public function __construct()
    {
        $this->auditLogService = service('auditLogService');
    }

    public function index()
    {
        // Audit hanya dicatat kalau memang ada yang sedang login
        if (session()->get('logged_in')) {
            $this->auditLogService->catat(
                session()->get('id_user'),
                session()->get('username'),
                'logout',
                ''
            );
        }

        session()->destroy();

        return redirect()->to('/login')
                          ->with('success', 'Anda berhasil logout.');
    }
}