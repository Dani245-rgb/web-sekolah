<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        return 'Dashboard Admin - ' . session()->get('username') . ' <br><a href="' . base_url('logout') . '">Logout</a>';
    }
}