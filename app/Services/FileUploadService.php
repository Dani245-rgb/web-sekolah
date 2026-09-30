<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Urusan upload & hapus file di folder public/uploads/<folder>.
 * Dipakai bersama oleh semua controller yang punya foto/file,
 * supaya logikanya (cek folder, cek permission, pindah file) cuma ada di satu tempat.
 */
class FileUploadService
{
    public const MIME_GAMBAR = ['image/jpeg', 'image/png', 'image/webp'];
    public const MAKS_BYTES  = 2 * 1024 * 1024; // 2MB

    /**
     * Simpan file upload ke public/uploads/<folder>.
     * Return nama file baru, atau null kalau tidak ada file yang diupload.
     * Melempar RuntimeException kalau gagal (folder tidak bisa ditulis, dll),
     * biar controller bisa rollback transaksi.
     */
    public function simpan(
        ?UploadedFile $file,
        string $folder,
        array $mimeDiizinkan = self::MIME_GAMBAR,
        int $maksBytes = self::MAKS_BYTES
    ): ?string {
        if ($file === null || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        // getMimeType() membaca isi file asli, bukan nama/ekstensi kiriman browser
        $mime = (string) $file->getMimeType();
        if (!in_array($mime, $mimeDiizinkan, true)) {
            throw new InvalidArgumentException("Tipe file tidak diizinkan (terdeteksi: {$mime}).");
        }

        if ($file->getSize() > $maksBytes) {
            $maksMb = round($maksBytes / 1024 / 1024, 1);
            throw new InvalidArgumentException("Ukuran file maksimal {$maksMb}MB.");
        }

        $targetDir = $this->pathFolder($folder);

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new RuntimeException('Folder upload tidak dapat dibuat.');
            }
        }

        if (!is_writable($targetDir)) {
            throw new RuntimeException('Folder upload tidak dapat ditulis (cek permission).');
        }

        $namaBaru = $file->getRandomName();

        if (!$file->move($targetDir, $namaBaru)) {
            throw new RuntimeException('Gagal memindahkan file upload.');
        }

        return $namaBaru;
    }

    /**
     * Hapus file dari public/uploads/<folder>. Aman dipanggil dengan nama kosong
     * atau file yang sudah tidak ada.
     */
    public function hapus(?string $namaFile, string $folder): void
    {
        if (empty($namaFile)) {
            return;
        }

        // basename() mencegah nama file berisi "../" keluar dari folder upload
        $path = $this->pathFolder($folder) . DIRECTORY_SEPARATOR . basename($namaFile);

        if (is_file($path)) {
            unlink($path);
        }
    }

    /**
     * Nama folder polos (mis. 'jurusan') otomatis jadi public/uploads/jurusan.
     * Kalau lokasinya di luar uploads, tulis path lengkap dari public/
     * (mis. 'assets/images/berita').
     */
    private function pathFolder(string $folder): string
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');

        if ($folder === '' || str_contains($folder, '..')) {
            throw new InvalidArgumentException('Nama folder upload tidak valid.');
        }

        $relatif = str_contains($folder, '/') ? $folder : 'uploads/' . $folder;

        return FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relatif);
    }
}
