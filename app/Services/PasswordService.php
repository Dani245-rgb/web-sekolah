<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\UserModel;

/**
 * Urusan ganti password mandiri (menu Profil).
 */
class PasswordService
{
    public function __construct(
        protected UserModel $userModel,
        protected AuditLogModel $auditLogModel,
    ) {
    }

    /**
     * Ganti password user yang sedang login, dengan memasukkan password lama.
     * Return daftar error per kolom, array kosong kalau berhasil.
     */
    public function gantiMandiri(int $idUser, string $passwordLama, string $passwordBaru): array
    {
        $user = $this->userModel->find($idUser);

        if (!$user) {
            return ['password_lama' => 'Akun tidak ditemukan.'];
        }

        // Password lama harus cocok dulu
        if (!password_verify($passwordLama, $user['password'])) {
            return ['password_lama' => 'Password lama salah.'];
        }

        // Tidak boleh sama dengan username
        if (strcasecmp($passwordBaru, $user['username']) === 0) {
            return ['password_baru' => 'Password tidak boleh sama dengan username.'];
        }

        // Tidak boleh sama persis dengan password lama
        if (password_verify($passwordBaru, $user['password'])) {
            return ['password_baru' => 'Password baru tidak boleh sama dengan password lama.'];
        }

        $berhasil = $this->userModel->update($idUser, [
            'password' => password_hash($passwordBaru, PASSWORD_DEFAULT),
        ]);

        if (!$berhasil) {
            return ['password_baru' => 'Password baru gagal disimpan. Silakan coba lagi.'];
        }

        $this->auditLogModel->catat($idUser, $user['username'], 'ganti_password', 'Ganti password mandiri lewat menu Profil.');

        return [];
    }
}