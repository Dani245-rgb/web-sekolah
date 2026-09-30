<?php

namespace App\Services;

use Config\Database;
use RuntimeException;

/**
 * Urusan backup database: bikin file .sql lewat mysqldump, daftar, cari, dan hapus.
 * File disimpan di writable/backup/ (di luar folder public, tidak bisa diakses lewat URL).
 */
class BackupService
{
    private string $folder;
    private string $mysqldump;

    public function __construct(?string $folder = null)
    {
        $this->folder    = rtrim($folder ?? WRITEPATH . 'backup', '/\\') . DIRECTORY_SEPARATOR;
        // Path mysqldump dibaca dari .env (backup.mysqldumpPath), default 'mysqldump' dari PATH server
        $this->mysqldump = env('backup.mysqldumpPath') ?: 'mysqldump';
    }

    /**
     * Daftar file backup, terbaru duluan.
     */
    public function daftar(): array
    {
        $files = glob($this->folder . '*.sql') ?: [];
        $files = array_reverse($files);

        return array_map(static function (string $f): array {
            return [
                'nama'    => basename($f),
                'ukuran'  => round(filesize($f) / 1024, 1) . ' KB',
                'tanggal' => date('d M Y H:i', filemtime($f)),
            ];
        }, $files);
    }

    /**
     * Bikin backup baru. Return nama file, atau lempar RuntimeException kalau gagal.
     */
    public function buat(): string
    {
        if (!function_exists('exec')) {
            throw new RuntimeException('Fungsi exec() dinonaktifkan di server ini, backup tidak bisa dibuat dari aplikasi.');
        }

        $this->pastikanFolder();

        $db   = Database::connect();
        $nama = 'backup_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $db->database) . '_' . date('Y-m-d_His') . '.sql';
        $path = $this->folder . $nama;

        // Semua argumen dibungkus escapeshellarg() untuk cegah command injection.
        // --result-file dipakai (bukan ">") supaya pesan error tidak ikut masuk ke file .sql.
        $perintah = escapeshellarg($this->mysqldump)
            . ' --single-transaction'
            . ' -h ' . escapeshellarg((string) $db->hostname)
            . ' -P ' . escapeshellarg((string) ($db->port ?? 3306))
            . ' -u ' . escapeshellarg((string) $db->username)
            . ' --result-file=' . escapeshellarg($path)
            . ' ' . escapeshellarg((string) $db->database)
            . ' 2>&1';

        // Password lewat environment, bukan argumen, supaya tidak terlihat di daftar proses server
        $pakaiPassword = (string) $db->password !== '';
        if ($pakaiPassword) {
            putenv('MYSQL_PWD=' . $db->password);
        }

        try {
            exec($perintah, $output, $kode);
        } finally {
            if ($pakaiPassword) {
                putenv('MYSQL_PWD');
            }
        }

        if ($kode !== 0 || !is_file($path) || filesize($path) === 0) {
            if (is_file($path)) {
                unlink($path);
            }

            throw new RuntimeException(
                'Backup gagal. Pastikan mysqldump terpasang dan bisa diakses dari server. Detail: '
                . mb_substr(implode(' ', $output), 0, 300)
            );
        }

        return $nama;
    }

    /**
     * Path lengkap file backup, atau null kalau nama tidak valid / file tidak ada.
     */
    public function path(string $nama): ?string
    {
        $nama = basename($nama); // cegah path traversal

        if (!preg_match('/^[A-Za-z0-9._-]+\.sql$/', $nama) || str_contains($nama, '..')) {
            return null;
        }

        $path = $this->folder . $nama;

        return is_file($path) ? $path : null;
    }

    public function hapus(string $nama): bool
    {
        $path = $this->path($nama);

        return $path !== null && unlink($path);
    }

    private function pastikanFolder(): void
    {
        if (!is_dir($this->folder) && !mkdir($this->folder, 0755, true) && !is_dir($this->folder)) {
            throw new RuntimeException('Folder backup tidak dapat dibuat.');
        }
    }
}