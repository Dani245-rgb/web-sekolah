<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\AuthService;

class Login extends BaseController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = service('authService');
    }

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

        $hasil = $this->authService->login(
            (string) $this->request->getPost('username'),
            (string) $this->request->getPost('password')
        );

        if ($hasil['status'] !== AuthService::SUKSES) {
            return redirect()->back()->withInput()->with('errors', ['login' => $hasil['pesan']]);
        }

        $user = $hasil['user'];

        // true = session lama dibuang sepenuhnya, jadi session ID sebelum login tidak bisa dipakai lagi
        session()->regenerate(true);

        session()->set([
            'id_user'              => $user['id_user'],
            'username'             => $user['username'],
            'role'                 => $user['nama_role'],
            'role_id'              => $user['role_id'],
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
        $idUser = session()->get('id_user');

        // Tanpa session login, jangan lanjut ke pembaruan password
        if (!$idUser) {
            return redirect()->to('/login');
        }

        $validation = $this->validate([
            'password_baru'       => 'required|strongPassword',
            'konfirmasi_password' => 'required|matches[password_baru]',
        ]);

        if (!$validation) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $userModel    = new UserModel();
        $passwordBaru = $this->request->getPost('password_baru');

        // Cegah ganti ke password yang sama persis dengan username
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
            default => '/login',
        };
    }
}