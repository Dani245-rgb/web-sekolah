<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
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
            'must_change_password' => false,
            'is_superadmin'        => (bool) ($user['is_superadmin'] ?? false),
        ]);

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
            default => '/login',
        };
    }
}