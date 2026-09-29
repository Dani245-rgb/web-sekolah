<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\NotifikasiModel;
use App\Models\UserModel;

/**
 * Aturan login: cek akun terkunci, cek kredensial, hitung percobaan gagal,
 * kunci akun, dan catat audit log. Controller cukup memanggil login() lalu
 * memetakan hasilnya ke redirect / session.
 */
class AuthService
{
    public const MAKS_PERCOBAAN     = 5;
    public const DURASI_KUNCI_DETIK = 15 * 60;

    public const SUKSES       = 'sukses';
    public const TERKUNCI     = 'terkunci';
    public const NONAKTIF     = 'nonaktif';
    public const ROLE_DITOLAK = 'role_ditolak';
    public const GAGAL        = 'gagal';

    public function __construct(
        protected UserModel $userModel,
        protected AuditLogModel $auditLogModel,
        protected NotifikasiModel $notifikasiModel,
    ) {
    }

    /**
     * @return array{status: string, user: ?array, pesan: string}
     */
    public function login(string $username, string $password): array
    {
        $calon = $this->userModel->where('username', $username)->first();

        if ($calon && $calon['locked_until'] && strtotime($calon['locked_until']) > time()) {
            $sisaMenit = (int) ceil((strtotime($calon['locked_until']) - time()) / 60);
            $this->auditLogModel->catat($calon['id_user'], $username, 'login_ditolak_terkunci', 'Percobaan login saat akun masih terkunci.');

            return $this->hasil(
                self::TERKUNCI,
                null,
                "Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam {$sisaMenit} menit."
            );
        }

        $user = $this->userModel->verifyCredentials($username, $password);

        // Password benar tapi akun nonaktif: JANGAN hitung sebagai percobaan gagal
        if ($user === 'inactive') {
            $this->auditLogModel->catat($calon['id_user'], $username, 'login_ditolak_nonaktif', 'Password benar, tapi akun berstatus nonaktif.');

            return $this->hasil(self::NONAKTIF, null, 'Akun ini sudah tidak aktif. Silakan hubungi Admin.');
        }

        // Hanya role Admin yang punya portal. Role lain ditolak.
        if ($user && $user['nama_role'] !== 'Admin') {
            $this->auditLogModel->catat(
                $user['id_user'],
                $username,
                'login_ditolak_role_belum_tersedia',
                'Role ' . $user['nama_role'] . ' mencoba login, portal belum tersedia.'
            );

            return $this->hasil(self::ROLE_DITOLAK, null, 'Portal untuk role ini belum tersedia. Silakan hubungi Admin.');
        }

        if (!$user) {
            if ($calon) {
                $this->catatGagal($calon, $username);
            }

            return $this->hasil(self::GAGAL, null, 'Username atau password salah, atau akun tidak aktif.');
        }

        $this->userModel->update($user['id_user'], ['login_attempts' => 0, 'locked_until' => null]);
        $this->auditLogModel->catat($user['id_user'], $user['username'], 'login_sukses', '');

        return $this->hasil(self::SUKSES, $user, '');
    }

    private function catatGagal(array $calon, string $username): void
    {
        // Increment atomik di level database, menghindari race condition
        // kalau ada beberapa request login gagal masuk bersamaan
        $berhasilUpdate = $this->userModel->where('id_user', $calon['id_user'])
            ->set('login_attempts', 'login_attempts + 1', false)
            ->update();

        // TEMPORARY DEBUG: hapus blok ini setelah masalah increment ditemukan
        log_message('error', 'DEBUG login_attempts update: ' . var_export($berhasilUpdate, true)
            . ' | model errors: ' . json_encode($this->userModel->errors())
            . ' | db error: ' . json_encode($this->userModel->db->error()));

        // Ambil ulang nilai terbaru SETELAH increment atomik di atas
        $calonTerbaru = $this->userModel->find($calon['id_user']);
        if (!$calonTerbaru) {
            return;
        }

        $attempts = (int) $calonTerbaru['login_attempts'];

        $this->auditLogModel->catat($calon['id_user'], $username, 'login_gagal', "Percobaan ke-{$attempts}.");

        if ($attempts < self::MAKS_PERCOBAAN) {
            return;
        }

        $menit = self::DURASI_KUNCI_DETIK / 60;

        $this->userModel->update($calon['id_user'], [
            'locked_until'   => date('Y-m-d H:i:s', time() + self::DURASI_KUNCI_DETIK),
            'login_attempts' => 0,
        ]);

        $this->auditLogModel->catat(
            $calon['id_user'],
            $username,
            'akun_terkunci',
            "Terkunci {$menit} menit setelah " . self::MAKS_PERCOBAAN . 'x gagal berturut-turut.'
        );

        $this->notifikasiModel->buat(
            null,
            'Akun Terkunci',
            'akun_terkunci',
            "Akun {$username} terkunci setelah " . self::MAKS_PERCOBAAN . 'x gagal login berturut-turut.',
            '/admin/user'
        );
    }

    private function hasil(string $status, ?array $user, string $pesan): array
    {
        return ['status' => $status, 'user' => $user, 'pesan' => $pesan];
    }
}