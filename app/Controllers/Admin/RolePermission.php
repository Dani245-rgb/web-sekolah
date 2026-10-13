<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class RolePermission extends BaseController
{
    public function index()
    {
        $data['roles'] = config('HakAkses')->roles;

        return view('admin/role_permission/index', $data);
    }
}