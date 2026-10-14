<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardService;

class Dashboard extends BaseController
{
    protected DashboardService $dashboard;

    public function __construct()
    {
        $this->dashboard = service('dashboardService');
    }

    public function index()
    {
        $admin = session()->get('role') === 'Admin';

        $data = $this->dashboard->ringkasan(
            (int) session()->get('id_user'),
            $admin && (bool) session()->get('is_superadmin'),
            $admin
        );

        $data['title'] = 'Dashboard';

        return view('admin/dashboard/index', $data);
    }
}