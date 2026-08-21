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
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress()), 3, 600) === false) {
            return redirect()->back()->withInput()
                ->with('errors', ['limit' => 'Terlalu banyak percobaan pendaftaran. Silakan coba lagi dalam beberapa menit.']);
        }

        $data = [
            'nama_lengkap'    => $this->request->getPost('nama_lengkap'),
            'tempat_lahir'    => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'   => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin'   => $this->request->getPost('jenis_kelamin'),
            'asal_sekolah'    => $this->request->getPost('asal_sekolah'),
            'jurusan_pilihan' => $this->request->getPost('jurusan_pilihan'),
            'no_hp'           => $this->request->getPost('no_hp'),
            'email'           => $this->request->getPost('email'),
            'alamat'          => $this->request->getPost('alamat'),
        ];

        $idPendaftar = $this->ppdbModel->insert($data);

        if (!$idPendaftar) {
            $errors = $this->ppdbModel->errors();
            return redirect()->back()->withInput()
                ->with('errors', $errors ?: ['gagal' => 'Pendaftaran gagal disimpan.']);
        }

        (new \App\Models\NotifikasiModel())->buat(
            null,
            'Pendaftar PPDB Baru',
            'ppdb_baru',
            'Ada pendaftar baru: ' . $this->request->getPost('nama_lengkap'),
            '/admin/ppdb/detail/' . $idPendaftar
        );

        return redirect()->to('/ppdb')->with('success', 'Pendaftaran berhasil dikirim! Kami akan menghubungi Anda segera.');
    }
}