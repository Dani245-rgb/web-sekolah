<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\AuditLogModel;

class User extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        if (!session()->get('is_superadmin')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Halaman tidak ditemukan.');
        }

        $this->userModel = new UserModel();
    }

    public function index(string $role = 'admin')
    {
        $roleMap = [
            'admin' => 1,
            'guru'  => 2,
            'siswa' => 3,
        ];

        $roleId     = $roleMap[$role] ?? 1;
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
        if (!session()->get('is_superadmin')) {
            return redirect()->to('/admin/user')->with('error', 'Hanya superadmin yang bisa menambah akun admin.');
        }
        return view('admin/user/create_admin');
    }

    public function storeAdmin()
    {
        if (!session()->get('is_superadmin')) {
            return redirect()->to('/admin/user')->with('error', 'Hanya superadmin yang bisa menambah akun admin.');
        }

        $rules = [
            'username' => 'required|min_length[4]|is_unique[users.username]',
            'password' => 'required|strongPassword',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->userModel->insert([
            'username'              => $this->request->getPost('username'),
            'password'              => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id'               => 1, // sesuai role_id Admin (guru=2, siswa=3 dari kode Guru/Siswa kamu)
            'status'                => 'Aktif',
            'must_change_password'  => 1,
            'login_attempts'        => 0,
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

        $this->userModel->unlockAccount($id);

        $auditLogModel = new AuditLogModel();
        $auditLogModel->catat(
            session()->get('id_user'),
            session()->get('username'),
            'unlock_akun',
            "Membuka kunci akun {$user['username']}."
        );

        return redirect()->to('/admin/user')->with('success', 'Akun berhasil dibuka kembali.');
    }

    /**
     * Reset password akun apapun (Admin/Guru/Siswa), lewat user_id langsung.
     * Siswa: password baru = tanggal lahir (ddmmyyyy), sesuai pola lama.
     * Guru/Admin: password baru = acak 8 karakter.
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

        $auditLogModel = new AuditLogModel();
        $auditLogModel->catat(
            session()->get('id_user'),
            session()->get('username'),
            'reset_password_admin',
            "Reset password akun {$namaTampil} ({$user['username']})."
        );

        return redirect()->to('/admin/user')
            ->with('success', "Password {$namaTampil} berhasil direset. Password baru: {$passwordBaru}. Wajib ganti password saat login berikutnya.");
    }
}
