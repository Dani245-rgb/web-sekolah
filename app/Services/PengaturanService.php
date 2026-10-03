<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\PengaturanModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Aturan bisnis Pengaturan Sekolah: identitas sekolah, logo, favicon, dan pencatatan audit.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class PengaturanService
{
    private const FOLDER       = 'assets/uploads/sekolah'; // public/assets/uploads/sekolah
    private const MIME_FAVICON = ['image/jpeg', 'image/png'];
    private const MAKS_FAVICON = 512 * 1024; // 512KB

    public function __construct(
        private PengaturanModel $model,
        private AuditLogModel $auditLog,
        private FileUploadService $uploader,
    ) {
        helper('teks');
    }

    public function ambil(): ?array
    {
        return $this->model->getPengaturan();
    }

    public function simpan(
        array $input,
        ?UploadedFile $logo,
        ?UploadedFile $favicon,
        $idUser,
        $username
    ): void {
        $pengaturan = $this->ambil() ?? throw new InvalidArgumentException('Data pengaturan belum ada di database.');

        $data = [
            'nama_sekolah' => rapikan_teks((string) ($input['nama_sekolah'] ?? '')),
            'alamat'       => $input['alamat'] ?? null,
            'telepon'      => rapikan_teks((string) ($input['telepon'] ?? '')),
            'email'        => $input['email'] ?? null,
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        // Logo & favicon (opsional). Kalau tidak diisi, file lama dipertahankan.
        $logoBaru = $this->unggah('Logo', fn () => $this->uploader->simpan($logo, self::FOLDER));

        try {
            $faviconBaru = $this->unggah('Favicon', fn () => $this->uploader->simpan(
                $favicon,
                self::FOLDER,
                self::MIME_FAVICON,
                self::MAKS_FAVICON
            ));
        } catch (InvalidArgumentException $e) {
            // Logo sudah terlanjur diupload, buang lagi
            $this->uploader->hapus($logoBaru, self::FOLDER);

            throw $e;
        }

        if ($logoBaru !== null) {
            $data['logo'] = $logoBaru;
        }
        if ($faviconBaru !== null) {
            $data['favicon'] = $faviconBaru;
        }

        if (!$this->model->update($pengaturan['id'], $data)) {
            $this->uploader->hapus($logoBaru, self::FOLDER);
            $this->uploader->hapus($faviconBaru, self::FOLDER);

            $errors = $this->model->errors();

            throw new InvalidArgumentException($errors ? implode('; ', $errors) : 'Gagal menyimpan pengaturan.');
        }

        // File lama baru dihapus SETELAH update DB sukses
        if ($logoBaru !== null) {
            $this->uploader->hapus($pengaturan['logo'] ?? null, self::FOLDER);
        }
        if ($faviconBaru !== null) {
            $this->uploader->hapus($pengaturan['favicon'] ?? null, self::FOLDER);
        }

        $this->auditLog->catat($idUser, $username, 'update_pengaturan', 'Mengubah pengaturan identitas sekolah.');
    }

    // ================= Helper privat =================

    /**
     * Jalankan upload satu file. Pesan dari uploader (tipe/ukuran salah) diteruskan ke user
     * dengan awalan nama file supaya jelas logo atau favicon yang bermasalah.
     */
    private function unggah(string $label, callable $aksi): ?string
    {
        try {
            return $aksi();
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("{$label}: " . $e->getMessage());
        } catch (RuntimeException $e) {
            log_message('error', "Gagal upload {$label} Pengaturan: " . $e->getMessage());

            throw new InvalidArgumentException("Gagal menyimpan {$label}. Silakan coba lagi.");
        }
    }
}