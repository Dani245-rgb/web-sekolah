<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $data['nama'] = session()->get('username');
        return view('siswa/dashboard', $data);
    }
}