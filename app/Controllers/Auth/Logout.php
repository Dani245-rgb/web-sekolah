<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class Logout extends BaseController
{
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        $this->auditLogModel = new AuditLogModel();
    }

    public function index()
    {
        // Audit hanya dicatat kalau memang ada yang sedang login
        if (session()->get('logged_in')) {
            $this->auditLogModel->catat(
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