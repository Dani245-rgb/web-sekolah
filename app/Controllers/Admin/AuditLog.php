<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class AuditLog extends BaseController
{
    protected AuditLogService $auditLogService;

    public function __construct()
    {
        $this->auditLogService = service('auditLogService');
    }

    public function index()
    {
        $filter = $this->ambilFilter();

        $data           = $this->auditLogService->halaman($filter, 30);
        $data['filter'] = $filter;

        return view('admin/audit_log/index', $data);
    }

    public function exportPdf()
    {
        try {
            $filter = $this->ambilFilter();
            $logs   = $this->auditLogService->semua($filter);

            $html = view('admin/audit_log/pdf', [
                'logs'   => $logs,
                'filter' => $filter,
            ]);

            $isi = $this->auditLogService->buatPdf($html);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Export PDF gagal dibuat.');
        }

        return $this->response->download('audit-log-' . date('Y-m-d_His') . '.pdf', $isi, true);
    }

    public function exportExcel()
    {
        try {
            $logs = $this->auditLogService->semua($this->ambilFilter());
            $isi  = $this->auditLogService->buatExcel($logs);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Export Excel gagal dibuat.');
        }

        return $this->response->download('audit-log-' . date('Y-m-d_His') . '.xlsx', $isi, true);
    }

    /**
     * Filter dari query string (dipakai bareng oleh index & export).
     */
    private function ambilFilter(): array
    {
        return $this->auditLogService->filterDariInput([
            'aksi'     => $this->request->getGet('aksi'),
            'username' => $this->request->getGet('username'),
            'dari'     => $this->request->getGet('dari'),
            'sampai'   => $this->request->getGet('sampai'),
        ]);
    }

    /**
     * Semua kegagalan di sini error sistem: log, tampilkan pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum)
    {
        log_message('error', '[AuditLog] ' . $e->getMessage());

        return redirect()->to('/admin/audit-log')->with('error', $pesanUmum);
    }
}