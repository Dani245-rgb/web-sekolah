<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\UserService;
use CodeIgniter\Exceptions\PageNotFoundException;
use InvalidArgumentException;
use Throwable;

class User extends BaseController
{
    protected UserService $user;

    public function __construct()
    {
        // Halaman ini khusus superadmin. Non-superadmin dianggap seolah rute tidak ada.
        if (!session()->get('is_superadmin')) {
            throw new PageNotFoundException('Halaman tidak ditemukan.');
        }

        $this->user = service('userService');
    }

    public function index(string $role = 'admin')
    {
        return view('admin/user/index', $this->user->daftar($role, $this->request->getGet('q')));
    }

    public function createAdmin()
    {
        return view('admin/user/create_admin');
    }

    public function storeAdmin()
    {
        $rules = [
            'username' => 'required|min_length[4]|is_unique[users.username]',
            'password' => 'required|strongPassword',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->user->buatAdmin($this->request->getPost(['username', 'password']));
        } catch (Throwable $e) {
            log_message('error', 'Gagal buat akun admin: ' . $e->getMessage());

            $pesan = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal membuat akun admin. Silakan coba lagi.';

            return redirect()->back()->withInput()->with('errors', ['user' => $pesan]);
        }

        return redirect()->to('/admin/user')->with('success', 'Akun admin baru berhasil dibuat.');
    }

    public function unlock($id)
    {
        try {
            $this->user->bukaKunci((int) $id, session()->get('id_user'), session()->get('username'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal membuka kunci akun. Silakan coba lagi.', 'unlock akun');
        }

        return redirect()->to('/admin/user')->with('success', 'Akun berhasil dibuka kembali.');
    }

    /**
     * Reset password akun apa pun lewat id user. Password baru acak 8 karakter,
     * ditampilkan sekali ke superadmin untuk disampaikan ke yang bersangkutan.
     */
    public function resetPassword($idUser)
    {
        try {
            $hasil = $this->user->resetPassword((int) $idUser, session()->get('id_user'), session()->get('username'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal mereset password. Silakan coba lagi.', 'reset password');
        }

        return redirect()->to('/admin/user')
            ->with('success', "Password {$hasil['username']} berhasil direset. Password baru: {$hasil['password']}. Sampaikan ke yang bersangkutan, lalu sarankan menggantinya lewat ikon kunci di pojok kanan atas.");
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi}: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return redirect()->to('/admin/user')->with('error', $pesan);
    }
}