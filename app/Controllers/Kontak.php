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
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress()), 3, 600) === false) {
            return redirect()->back()->withInput()
                ->with('errors', ['limit' => 'Terlalu banyak percobaan pengiriman pesan. Silakan coba lagi dalam beberapa menit.']);
        }

        $rules = [
            'nama'  => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email',
            'pesan' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idPesan = $this->kontakModel->insert([
            'nama'   => $this->request->getPost('nama'),
            'email'  => $this->request->getPost('email'),
            'subjek' => $this->request->getPost('subjek'),
            'pesan'  => $this->request->getPost('pesan'),
        ]);

        (new \App\Models\NotifikasiModel())->buat(
            null,
            'Pesan Masuk Baru',
            'pesan_masuk',
            'Pesan dari ' . $this->request->getPost('nama') . ': ' . $this->request->getPost('subjek'),
            '/admin/kontak/detail/' . $idPesan
        );

        return redirect()->to('/kontak')->with('success', 'Pesan Anda berhasil dikirim. Terima kasih!');
    }
}
