<?php

namespace App\Controllers;

use App\Services\PasswordService;

class Profil extends BaseController
{
    protected PasswordService $password;

    public function __construct()
    {
        $this->password = service('passwordService');
    }

    public function gantipassword()
    {
        return view('profil/ganti_password');
    }

    public function gantipasswordsubmit()
    {
        $rules = [
            'password_lama'       => 'required',
            'password_baru'       => 'required|strongPassword',
            'konfirmasi_password' => 'required|matches[password_baru]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $errors = $this->password->gantiMandiri(
            (int) session()->get('id_user'),
            (string) $this->request->getPost('password_lama'),
            (string) $this->request->getPost('password_baru')
        );

        if ($errors) {
            return redirect()->back()->with('errors', $errors);
        }

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }
}