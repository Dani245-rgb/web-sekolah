<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AuditLogService;
use App\Services\BackupService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

class BackupDatabase extends BaseController
{
    protected BackupService $backup;
    protected AuditLogService $auditLogService;

    public function __construct()
    {
        if (!session()->get('is_superadmin')) {
            throw new PageNotFoundException('Halaman tidak ditemukan.');
        }

        $this->backup          = service('backupService');
        $this->auditLogService = service('auditLogService');
    }

    public function index()
    {
        $data['backups'] = $this->backup->daftar();

        return view('admin/backup_database/index', $data);
    }

    public function create()
    {
        try {
            $nama = $this->backup->buat();
        } catch (RuntimeException $e) {
            return redirect()->to('/admin/backup-database')->with('error', $e->getMessage());
        }

        $this->catatAudit('backup_database', "Membuat backup database: {$nama}");

        return redirect()->to('/admin/backup-database')->with('success', "Backup berhasil dibuat: {$nama}");
    }

    public function download(string $filename)
    {
        $path = $this->backup->path($filename);

        if ($path === null) {
            return redirect()->to('/admin/backup-database')->with('error', 'File backup tidak ditemukan.');
        }

        return $this->response->download($path, null);
    }

    public function delete(string $filename)
    {
        $nama = basename($filename);

        if (!$this->backup->hapus($nama)) {
            return redirect()->to('/admin/backup-database')->with('error', 'File backup tidak ditemukan.');
        }

        $this->catatAudit('hapus_backup_database', "Menghapus file backup: {$nama}");

        return redirect()->to('/admin/backup-database')->with('success', 'Backup berhasil dihapus.');
    }

    private function catatAudit(string $aksi, string $keterangan): void
    {
        $this->auditLogService->catat(
            session()->get('id_user'),
            session()->get('username'),
            $aksi,
            $keterangan
        );
    }
}