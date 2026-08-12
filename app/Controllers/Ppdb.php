<?php

namespace App\Controllers;

use App\Models\PendaftarPpdbModel;

class Ppdb extends BaseController
{
    protected $ppdbModel;

    public function __construct()
    {
        $this->ppdbModel = new PendaftarPpdbModel();
    }

    public function index()
    {
        return view('ppdb/index');
    }

    public function daftar()
    {
        $rules = [
            'nama_lengkap'    => 'required|min_length[3]|max_length[255]',
            'jenis_kelamin'   => 'required|in_list[Laki-laki,Perempuan]',
            'jurusan_pilihan' => 'required',
            'no_hp'           => 'required|min_length[9]|max_length[20]',
            'email'           => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->ppdbModel->insert([
            'nama_lengkap'    => $this->request->getPost('nama_lengkap'),
            'tempat_lahir'    => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'   => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin'   => $this->request->getPost('jenis_kelamin'),
            'asal_sekolah'    => $this->request->getPost('asal_sekolah'),
            'jurusan_pilihan' => $this->request->getPost('jurusan_pilihan'),
            'no_hp'           => $this->request->getPost('no_hp'),
            'email'           => $this->request->getPost('email'),
            'alamat'          => $this->request->getPost('alamat'),
        ]);

        return redirect()->to('/ppdb')->with('success', 'Pendaftaran berhasil dikirim! Kami akan menghubungi Anda segera.');
    }
}