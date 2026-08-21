<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class BackupDatabase extends BaseController
{
    protected string $backupPath;

    public function __construct()
    {
        if (!session()->get('is_superadmin')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Halaman tidak ditemukan.');
        }

        $this->backupPath = WRITEPATH . 'backup/';

        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    public function index()
    {
        $files = glob($this->backupPath . '*.sql');
        $files = array_reverse($files); // terbaru duluan

        $data['backups'] = array_map(function ($f) {
            return [
                'nama'    => basename($f),
                'ukuran'  => round(filesize($f) / 1024, 1) . ' KB',
                'tanggal' => date('d M Y H:i', filemtime($f)),
            ];
        }, $files);

        return view('admin/backup_database/index', $data);
    }

    public function create()
    {
        $db = \Config\Database::connect();
        $config = $db->getConnection() ? $db : null;

        $hostname = $db->hostname;
        $username = $db->username;
        $password = $db->password;
        $database = $db->database;
        $port     = $db->port ?? 3306;

        $filename = 'backup_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $database) . '_' . date('Y-m-d_His') . '.sql';
        $filepath = $this->backupPath . $filename;

        // Command mysqldump — semua argumen dibungkus escapeshellarg() untuk cegah command injection
        $mysqldumpPath = 'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe';
        $passwordPart  = $password !== '' ? '-p' . escapeshellarg($password) : '';
        $command = escapeshellarg($mysqldumpPath)
            . ' -h ' . escapeshellarg($hostname)
            . ' -P ' . escapeshellarg((string) $port)
            . ' -u ' . escapeshellarg($username)
            . ' ' . $passwordPart
            . ' ' . escapeshellarg($database)
            . ' > ' . escapeshellarg($filepath) . ' 2>&1';

        exec($command, $output, $resultCode);

        if ($resultCode !== 0 || !file_exists($filepath) || filesize($filepath) === 0) {
            // Bersihkan file gagal/kosong kalau ada
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            return redirect()->to('/admin/backup-database')
                ->with('error', 'Backup gagal. Pastikan mysqldump terpasang dan bisa diakses dari server. Detail: ' . implode(' ', $output));
        }

        (new \App\Models\AuditLogModel())->catat(
            session()->get('id_user'),
            session()->get('username'),
            'backup_database',
            "Membuat backup database: {$filename}"
        );

        return redirect()->to('/admin/backup-database')->with('success', "Backup berhasil dibuat: {$filename}");
    }

    public function download(string $filename)
    {
        $filename = basename($filename); // cegah path traversal
        $filepath = $this->backupPath . $filename;

        if (!file_exists($filepath)) {
            return redirect()->to('/admin/backup-database')->with('error', 'File backup tidak ditemukan.');
        }

        return $this->response->download($filepath, null);
    }

    public function delete(string $filename)
    {
        $filename = basename($filename);
        $filepath = $this->backupPath . $filename;

        if (file_exists($filepath)) {
            unlink($filepath);

            (new \App\Models\AuditLogModel())->catat(
                session()->get('id_user'),
                session()->get('username'),
                'hapus_backup_database',
                "Menghapus file backup: {$filename}"
            );

            return redirect()->to('/admin/backup-database')->with('success', 'Backup berhasil dihapus.');
        }

        return redirect()->to('/admin/backup-database')->with('error', 'File backup tidak ditemukan.');
    }
}
