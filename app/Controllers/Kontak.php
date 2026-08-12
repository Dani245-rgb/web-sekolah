<?php

namespace App\Controllers;

use App\Models\PesanKontakModel;

class Kontak extends BaseController
{
    protected $kontakModel;

    public function __construct()
    {
        $this->kontakModel = new PesanKontakModel();
    }

    public function index()
    {
        return view('kontak/index');
    }

    public function kirim()
    {
        $rules = [
            'nama'  => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email',
            'pesan' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->kontakModel->insert([
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'subjek' => $this->request->getPost('subjek'),
            'pesan'  => $this->request->getPost('pesan'),
        ]);

        return redirect()->to('/kontak')->with('success', 'Pesan Anda berhasil dikirim. Terima kasih!');
    }
}