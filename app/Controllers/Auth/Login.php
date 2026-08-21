<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Login extends BaseController
{
    public function index()
    {
        // Kalau sudah login, jangan biarkan buka halaman login lagi
        if (session()->get('logged_in')) {
            return redirect()->to($this->redirectByRole(session()->get('role')));
        }

        return view('auth/login');
    }

    public function authenticate()
    {
        if (!$this->validate('login')) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel     = new UserModel();
        $auditLogModel = new \App\Models\AuditLogModel();
        $calon         = $userModel->where('username', $username)->first();

        if ($calon && $calon['locked_until'] && strtotime($calon['locked_until']) > time()) {
            $sisaMenit = (int) ceil((strtotime($calon['locked_until']) - time()) / 60);
            $auditLogModel->catat($calon['id_user'], $username, 'login_ditolak_terkunci', 'Percobaan login saat akun masih terkunci.');
            return redirect()->back()->withInput()
                ->with('errors', ['login' => "Akun terkunci sementara karena terlalu banyak percobaan gagal. Coba lagi dalam {$sisaMenit} menit."]);
        }

        $user = $userModel->verifyCredentials($username, $password);

        // Kasus khusus: password benar, tapi akun nonaktif -> JANGAN hitung sebagai percobaan gagal
        if ($user === 'inactive') {
            $auditLogModel->catat($calon['id_user'], $username, 'login_ditolak_nonaktif', 'Password benar, tapi akun berstatus nonaktif.');
            return redirect()->back()->withInput()
                ->with('errors', ['login' => 'Akun ini sudah tidak aktif. Silakan hubungi Admin.']);
        }

        if (!$user) {
            if ($calon) {
                // Increment atomik di level database — hindari race condition
                // kalau ada beberapa request login gagal masuk bersamaan
                $userModel->where('id_user', $calon['id_user'])
                    ->set('login_attempts', 'login_attempts + 1', false)
                    ->update();

                // Ambil ulang nilai terbaru SETELAH increment atomik di atas
                $calonTerbaru = $userModel->find($calon['id_user']);
                $attempts     = $calonTerbaru['login_attempts'];

                $auditLogModel->catat($calon['id_user'], $username, 'login_gagal', "Percobaan ke-{$attempts}.");

                if ($attempts >= 5) {
                    $userModel->update($calon['id_user'], [
                        'locked_until'   => date('Y-m-d H:i:s', time() + 15 * MINUTE),
                        'login_attempts' => 0,
                    ]);
                    $auditLogModel->catat($calon['id_user'], $username, 'akun_terkunci', 'Terkunci 15 menit setelah 5x gagal berturut-turut.');

                    (new \App\Models\NotifikasiModel())->buat(
                        null,
                        'Akun Terkunci',
                        'akun_terkunci',
                        "Akun {$username} terkunci setelah 5x gagal login berturut-turut.",
                        '/admin/user'
                    );
                }
            }

            return redirect()->back()->withInput()
                ->with('errors', ['login' => 'Username atau password salah, atau akun tidak aktif.']);
        }


        $userModel->update($user['id_user'], ['login_attempts' => 0, 'locked_until' => null]);
        $auditLogModel->catat($user['id_user'], $user['username'], 'login_sukses', '');

        session()->regenerate();

        session()->set([
            'id_user'              => $user['id_user'],
            'username'             => $user['username'],
            'role'                 => $user['nama_role'],
            'logged_in'            => true,
            'must_change_password' => (bool) $user['must_change_password'],
            'is_superadmin'        => (bool) ($user['is_superadmin'] ?? false),
        ]);

        if ($user['must_change_password']) {
            return redirect()->to('/auth/gantipassword')
                ->with('warning', 'Kamu wajib mengganti password sebelum melanjutkan.');
        }

        return redirect()->to($this->redirectByRole($user['nama_role']))
            ->with('success', 'Selamat datang, ' . $user['username'] . '!');
    }

    public function gantipassword()
    {
        return view('auth/ganti_password');
    }

    public function gantipasswordsubmit()
    {
        $validation = $this->validate([
            'password_baru' => 'required|strongPassword',
            'konfirmasi_password' => 'required|matches[password_baru]',
        ]);

        if (!$validation) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $userModel    = new UserModel();
        $idUser       = session()->get('id_user');
        $passwordBaru = $this->request->getPost('password_baru');

        // Cegah ganti ke password yang sama persis dengan username (mis. NIS)
        $user = $userModel->find($idUser);
        if (strcasecmp($passwordBaru, $user['username']) === 0) {
            return redirect()->back()
                ->with('errors', ['password_baru' => 'Password tidak boleh sama dengan username.']);
        }

        $userModel->update($idUser, [
            'password'             => password_hash($passwordBaru, PASSWORD_DEFAULT),
            'must_change_password' => false,
        ]);

        (new \App\Models\AuditLogModel())->catat($idUser, $user['username'], 'ganti_password_wajib', 'Ganti password pertama kali (dipaksa sistem).');

        session()->set('must_change_password', false);

        return redirect()->to($this->redirectByRole(session()->get('role')))
            ->with('success', 'Password berhasil diubah.');
    }
    /**
     * Tentukan tujuan redirect berdasarkan nama role.
     */
    private function redirectByRole(string $role): string
    {
        return match ($role) {
            'Admin' => '/admin/dashboard',
            'Guru'  => '/guru/dashboard',
            'Siswa' => '/siswa/dashboard',
            default => '/login',
        };
    }
}
