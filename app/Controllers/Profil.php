<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Profil extends BaseController
{
    public function gantipassword()
    {
        return view('profil/ganti_password');
    }

    public function gantipasswordsubmit()
    {
        $validation = $this->validate([
            'password_lama'       => 'required',
            'password_baru'       => 'required|min_length[6]',
            'konfirmasi_password' => 'required|matches[password_baru]',
        ]);

        if (!$validation) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $idUser    = session()->get('id_user');
        $user      = $userModel->find($idUser);

        $passwordLama = $this->request->getPost('password_lama');
        $passwordBaru = $this->request->getPost('password_baru');

        // Password lama harus cocok dulu
        if (!password_verify($passwordLama, $user['password'])) {
            return redirect()->back()->with('errors', ['password_lama' => 'Password lama salah.']);
        }

        // Tidak boleh sama dengan username
        if (strcasecmp($passwordBaru, $user['username']) === 0) {
            return redirect()->back()->with('errors', ['password_baru' => 'Password tidak boleh sama dengan username.']);
        }

        // Tidak boleh sama persis dengan password lama
        if (password_verify($passwordBaru, $user['password'])) {
            return redirect()->back()->with('errors', ['password_baru' => 'Password baru tidak boleh sama dengan password lama.']);
        }

        $userModel->update($idUser, [
            'password' => password_hash($passwordBaru, PASSWORD_DEFAULT),
        ]);

        (new \App\Models\AuditLogModel())->catat($idUser, $user['username'], 'ganti_password', 'Ganti password mandiri lewat menu Profil.');

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }
}