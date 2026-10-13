<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class RolePermission extends BaseController
{
    public function index()
    {
        $data['roles'] = config('HakAkses')->roles;

        return view('admin/role_permission/index', $data);
    }
}