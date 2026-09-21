<?php

namespace App\Controllers;

use App\Models\PendaftarPpdbModel;
use App\Models\PengaturanPpdbModel;

class Ppdb extends BaseController
{
    protected $ppdbModel;
    protected $settingModel;

    public function __construct()
    {
        $this->ppdbModel    = new PendaftarPpdbModel();
        $this->settingModel = new PengaturanPpdbModel();
    }

    public function index()
    {
        $setting = $this->settingModel->getSetting();
        $data['setting'] = $setting;

        // kalau tutup, tampilkan view khusus "belum dibuka" bukan form
        if ($setting['status'] !== 'buka') {
            return view('ppdb/tutup', $data);
        }

        return view('ppdb/index', $data);
    }

    public function daftar()
    {
        // validasi ulang di server, jangan cuma andalkan tampilan
        $setting = $this->settingModel->getSetting();
        if ($setting['status'] !== 'buka') {
            return redirect()->to('/ppdb')->with('error', 'Pendaftaran sedang ditutup.');
        }

        // Rate limiting sudah ditangani oleh filter route: throttle:ppdb,3,120 (lihat Routes.php)
        // Jadi tidak perlu dicek manual lagi di sini.

        // Validasi eksplisit di controller, jangan cuma andalkan rules di model
        $rules = [
            'nama_lengkap'    => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-Z\s\.\']+$/]',
            'tempat_lahir'    => 'required|max_length[100]',
            'tanggal_lahir'   => 'required|valid_date[Y-m-d]',
            'jenis_kelamin'   => 'required|in_list[Laki-laki,Perempuan]',
            'asal_sekolah'    => 'required|max_length[150]',
            'jurusan_pilihan' => 'required',
            'no_hp'           => 'required|numeric|min_length[10]|max_length[15]',
            'email'           => 'permit_empty|valid_email|max_length[100]',
            'alamat'          => 'required|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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
            'tahun_ajaran'    => $setting['tahun_ajaran'] ?? null,
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
