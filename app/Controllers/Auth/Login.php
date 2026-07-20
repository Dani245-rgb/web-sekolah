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
        // 1. Validasi input
        if (!$this->validate('login')) {
            return redirect()->back()
                              ->withInput()
                              ->with('errors', $this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // 2. Cek ke database lewat Model
        $userModel = new UserModel();
        $user = $userModel->verifyCredentials($username, $password);

        // 3. Kalau tidak ketemu / password salah / akun nonaktif
        if (!$user) {
            return redirect()->back()
                              ->withInput()
                              ->with('errors', ['login' => 'Username atau password salah, atau akun tidak aktif.']);
        }

        // 4. Simpan data ke Session (Tahap 11)
        session()->set([
            'id_user'   => $user['id_user'],
            'username'  => $user['username'],
            'role'      => $user['nama_role'],
            'logged_in' => true,
        ]);

        // 5. Redirect sesuai role
        return redirect()->to($this->redirectByRole($user['nama_role']))
                          ->with('success', 'Selamat datang, ' . $user['username'] . '!');
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