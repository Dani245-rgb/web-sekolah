<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class Logout extends BaseController
{
    public function index()
    {
        (new AuditLogModel())->catat(
            session()->get('id_user'),
            session()->get('username'),
            'logout',
            ''
        );

        session()->destroy();

        return redirect()->to('/login')
                          ->with('success', 'Anda berhasil logout.');
    }
}