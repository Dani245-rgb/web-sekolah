<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class AuditLog extends BaseController
{
    public function index()
    {
        $auditLogModel = new AuditLogModel();
        $data['logs']  = $auditLogModel->orderBy('created_at', 'DESC')->paginate(30);
        $data['pager'] = $auditLogModel->pager;
        return view('admin/audit_log/index', $data);
    }
}