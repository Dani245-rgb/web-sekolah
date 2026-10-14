<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\UserModel;
use InvalidArgumentException;

/**
 * Aturan bisnis manajemen akun (khusus superadmin): daftar akun, buat admin,
 * buka kunci akun, dan reset password. Aksi sensitif selalu dicatat ke audit log.
 *
 * Pelaku (id dan username superadmin yang sedang login) dikirim sebagai parameter,
 * service tidak membaca session.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class UserService
{
    private const ROLE_MAP = [
        'admin' => 1,
        'guru'  => 2,
        'siswa' => 3,
    ];

    private const PER_HALAMAN = 100;

    public function __construct(
        private UserModel $model,
        private AuditLogModel $auditLog,
    ) {
        helper('teks');
    }

    /**
     * @return array{users: array, pager: mixed, keyword: ?string, role: string, roleLabel: string, pagerGroup: string}
     */
    public function daftar(string $role = 'admin', ?string $keyword = null): array
    {
        $roleId     = self::ROLE_MAP[$role] ?? self::ROLE_MAP['admin'];
        $pagerGroup = 'user_' . $role;

        $users = $this->model->getAllWithDetail(self::PER_HALAMAN, $keyword, $roleId, $pagerGroup);

        return [
            'users'      => $users,
            'pager'      => $this->model->pager,
            'keyword'    => $keyword,
            'role'       => $role,
            'roleLabel'  => ucfirst($role),
            'pagerGroup' => $pagerGroup,
        ];
    }

    public function buatAdmin(array $input): void
    {
        $username = rapikan_teks((string) ($input['username'] ?? ''));

        if ($username === '') {
            throw new InvalidArgumentException('Username tidak boleh kosong/hanya spasi.');
        }

        if ($this->model->usernameSudahAda($username)) {
            throw new InvalidArgumentException('Username ini sudah terdaftar.');
        }

        $berhasil = $this->model->insert([
            'username'       => $username,
            'password'       => password_hash((string) ($input['password'] ?? ''), PASSWORD_DEFAULT),
            'role_id'        => self::ROLE_MAP['admin'],
            'status'         => 'Aktif',
            'login_attempts' => 0,
        ]);

        if (!$berhasil) {
            $errors = $this->model->errors();

            throw new InvalidArgumentException($errors ? implode('; ', $errors) : 'Gagal membuat akun admin.');
        }
    }

    public function buatContentManager(array $input): void
    {
        $roleId = $this->model->idRolePerNama('Content Manager')
            ?? throw new InvalidArgumentException('Role Content Manager belum ada. Jalankan migration dulu.');

        $username = rapikan_teks((string) ($input['username'] ?? ''));

        if ($username === '') {
            throw new InvalidArgumentException('Username tidak boleh kosong/hanya spasi.');
        }

        if ($this->model->usernameSudahAda($username)) {
            throw new InvalidArgumentException('Username ini sudah terdaftar.');
        }

        $berhasil = $this->model->insert([
            'username'       => $username,
            'password'       => password_hash((string) ($input['password'] ?? ''), PASSWORD_DEFAULT),
            'role_id'        => $roleId,
            'status'         => 'Aktif',
            'login_attempts' => 0,
        ]);

        if (!$berhasil) {
            $errors = $this->model->errors();

            throw new InvalidArgumentException($errors ? implode('; ', $errors) : 'Gagal membuat akun Content Manager.');
        }
    }

    public function bukaKunci(int $idTarget, $idPelaku, $usernamePelaku): void
    {
        if ($idTarget === (int) $idPelaku) {
            throw new InvalidArgumentException('Tidak bisa unlock akun sendiri lewat sini.');
        }

        $user = $this->model->find($idTarget) ?? throw new InvalidArgumentException('Akun tidak ditemukan.');

        if (!$this->model->unlockAccount($idTarget)) {
            throw new InvalidArgumentException('Gagal membuka kunci akun.');
        }

        $this->auditLog->catat($idPelaku, $usernamePelaku, 'unlock_akun', "Membuka kunci akun {$user['username']}.");
    }

    /**
     * Reset password akun apa pun (Admin/Guru/Siswa) lewat id user.
     * Password baru selalu acak 8 karakter dan dikembalikan supaya bisa disampaikan ke yang bersangkutan.
     *
     * @return array{username: string, password: string}
     */
    public function resetPassword(int $idTarget, $idPelaku, $usernamePelaku): array
    {
        if ($idTarget === (int) $idPelaku) {
            throw new InvalidArgumentException('Tidak bisa reset password akun sendiri lewat sini.');
        }

        $user = $this->model->find($idTarget) ?? throw new InvalidArgumentException('Akun tidak ditemukan.');

        $passwordBaru = bin2hex(random_bytes(4)); // 8 karakter acak

        $berhasil = $this->model->update($idTarget, [
            'password'       => password_hash($passwordBaru, PASSWORD_DEFAULT),
            'login_attempts' => 0,
            'locked_until'   => null,
        ]);

        if (!$berhasil) {
            throw new InvalidArgumentException('Gagal mereset password. Silakan coba lagi.');
        }

        $this->auditLog->catat(
            $idPelaku,
            $usernamePelaku,
            'reset_password_admin',
            "Reset password akun {$user['username']} ({$user['username']})."
        );

        return ['username' => $user['username'], 'password' => $passwordBaru];
    }
}
