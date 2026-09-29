<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class User extends BaseController
{
    private const ROLE_MAP = [
        'admin' => 1,
        'guru'  => 2,
        'siswa' => 3,
    ];

    protected UserModel $userModel;
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        helper('teks');

        // Halaman ini khusus superadmin. Non-superadmin dianggap seolah rute tidak ada.
        if (!session()->get('is_superadmin')) {
            throw new PageNotFoundException('Halaman tidak ditemukan.');
        }

        $this->userModel     = new UserModel();
        $this->auditLogModel = new AuditLogModel();
    }

    public function index(string $role = 'admin')
    {
        $roleId     = self::ROLE_MAP[$role] ?? 1;
        $pagerGroup = 'user_' . $role;
        $keyword    = $this->request->getGet('q');

        $data['users']      = $this->userModel->getAllWithDetail(100, $keyword, $roleId, $pagerGroup);
        $data['pager']      = $this->userModel->pager;
        $data['keyword']    = $keyword;
        $data['role']       = $role;
        $data['roleLabel']  = ucfirst($role);
        $data['pagerGroup'] = $pagerGroup;

        return view('admin/user/index', $data);
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

        $username = rapikan_teks($this->request->getPost('username'));

        if ($username === '') {
            return redirect()->back()->withInput()->with('error', 'Username tidak boleh kosong/hanya spasi.');
        }

        if ($this->userModel->where('username', $username)->first()) {
            return redirect()->back()->withInput()->with('errors', ['username' => 'Username ini sudah terdaftar.']);
        }

        $this->userModel->insert([
            'username'             => $username,
            'password'             => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id'              => self::ROLE_MAP['admin'],
            'status'               => 'Aktif',
            'must_change_password' => 1,
            'login_attempts'       => 0,
        ]);

        return redirect()->to('/admin/user')->with('success', 'Akun admin baru berhasil dibuat.');
    }

    public function unlock($id)
    {
        if ((int) $id === (int) session()->get('id_user')) {
            return redirect()->to('/admin/user')->with('error', 'Tidak bisa unlock akun sendiri lewat sini.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/admin/user')->with('error', 'Akun tidak ditemukan.');
        }

        $this->userModel->unlockAccount((int) $id);

        $this->auditLogModel->catat(
            session()->get('id_user'),
            session()->get('username'),
            'unlock_akun',
            "Membuka kunci akun {$user['username']}."
        );

        return redirect()->to('/admin/user')->with('success', 'Akun berhasil dibuka kembali.');
    }

    /**
     * Reset password akun apa pun (Admin/Guru/Siswa) lewat user_id.
     * Password baru selalu acak 8 karakter, ditandai wajib ganti password saat login berikutnya.
     */
    public function resetPassword($idUser)
    {
        if ((int) $idUser === (int) session()->get('id_user')) {
            return redirect()->to('/admin/user')->with('error', 'Tidak bisa reset password akun sendiri lewat sini.');
        }

        $user = $this->userModel->find($idUser);
        if (!$user) {
            return redirect()->to('/admin/user')->with('error', 'Akun tidak ditemukan.');
        }

        $namaTampil   = $user['username'];
        $passwordBaru = bin2hex(random_bytes(4)); // 8 karakter acak

        $this->userModel->update($idUser, [
            'password'              => password_hash($passwordBaru, PASSWORD_DEFAULT),
            'must_change_password'  => 1,
            'login_attempts'        => 0,
            'locked_until'          => null,
        ]);

        $this->auditLogModel->catat(
            session()->get('id_user'),
            session()->get('username'),
            'reset_password_admin',
            "Reset password akun {$namaTampil} ({$user['username']})."
        );

        return redirect()->to('/admin/user')
            ->with('success', "Password {$namaTampil} berhasil direset. Password baru: {$passwordBaru}. Wajib ganti password saat login berikutnya.");
    }
}