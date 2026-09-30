<?php

namespace App\Controllers;

use App\Services\PpdbService;

class Ppdb extends BaseController
{
    protected PpdbService $ppdb;

    public function __construct()
    {
        helper('teks');

        $this->ppdb = service('ppdbService');
    }

    public function index()
    {
        $data['setting'] = $this->ppdb->setting();

        // kalau tutup, tampilkan view khusus "belum dibuka" bukan form
        if (!$this->ppdb->dibuka()) {
            return view('ppdb/tutup', $data);
        }

        return view('ppdb/index', $data);
    }

    public function daftar()
    {
        // validasi ulang di server, jangan cuma andalkan tampilan
        if (!$this->ppdb->dibuka()) {
            return redirect()->to('/ppdb')->with('error', 'Pendaftaran sedang ditutup.');
        }

        // Rate limiting ditangani filter route: throttle:ppdb,3,120 (lihat Routes.php)

        $data = [
            'nama_lengkap'    => rapikan_teks((string) $this->request->getPost('nama_lengkap')),
            'tempat_lahir'    => rapikan_teks((string) $this->request->getPost('tempat_lahir')),
            'tanggal_lahir'   => trim((string) $this->request->getPost('tanggal_lahir')),
            'jenis_kelamin'   => $this->request->getPost('jenis_kelamin'),
            'asal_sekolah'    => rapikan_teks((string) $this->request->getPost('asal_sekolah')),
            'jurusan_pilihan' => $this->request->getPost('jurusan_pilihan'),
            'no_hp'           => trim((string) $this->request->getPost('no_hp')),
            'email'           => trim((string) $this->request->getPost('email')),
            'alamat'          => trim((string) $this->request->getPost('alamat')),
        ];

        $rules = [
            'nama_lengkap'    => 'required|min_length[3]|max_length[100]|regex_match[/^[a-zA-Z\s\.\']+$/]',
            'tempat_lahir'    => 'required|max_length[100]',
            'tanggal_lahir'   => 'required|valid_date[Y-m-d]',
            'jenis_kelamin'   => 'required|in_list[Laki-laki,Perempuan]',
            'asal_sekolah'    => 'required|max_length[150]',
            'jurusan_pilihan' => 'required',
            'no_hp'           => 'required|regex_match[/^\+?[0-9]{10,15}$/]',
            'email'           => 'permit_empty|valid_email|max_length[100]',
            'alamat'          => 'required|max_length[500]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $pesan = $this->ppdb->cekAturan($data);
        if ($pesan !== null) {
            return $this->kembaliDenganError(['tanggal_lahir' => $pesan]);
        }

        $id = $this->ppdb->daftar($data);

        if ($id === false) {
            return $this->kembaliDenganError(
                $this->ppdb->errors() ?: ['gagal' => 'Pendaftaran gagal disimpan.']
            );
        }

        return redirect()->to('/ppdb')->with('success', 'Pendaftaran berhasil dikirim! Kami akan menghubungi Anda segera.');
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}