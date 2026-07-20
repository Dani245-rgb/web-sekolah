<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;

class Logout extends BaseController
{
    public function index()
    {
        session()->destroy();

        return redirect()->to('/login')
                          ->with('success', 'Anda berhasil logout.');
    }
}