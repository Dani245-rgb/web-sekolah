<?php

namespace App\Controllers;

use App\Services\KontakService;

class Kontak extends BaseController
{
    protected KontakService $kontak;

    public function __construct()
    {
        helper('teks');

        $this->kontak = service('kontakService');
    }

    public function index()
    {
        return view('kontak/index');
    }

    public function kirim()
    {
        if ($this->terlaluBanyakKirim()) {
            return $this->kembaliDenganError([
                'limit' => 'Terlalu banyak percobaan pengiriman pesan. Silakan coba lagi dalam beberapa menit.',
            ]);
        }

        $data = [
            'nama'   => rapikan_teks((string) $this->request->getPost('nama')),
            'email'  => trim((string) $this->request->getPost('email')),
            'subjek' => rapikan_teks((string) $this->request->getPost('subjek')),
            'pesan'  => trim((string) $this->request->getPost('pesan')),
        ];

        $rules = [
            'nama'   => 'required|min_length[3]|max_length[255]',
            'email'  => 'required|valid_email',
            'subjek' => 'permit_empty|max_length[255]',
            'pesan'  => 'required|max_length[5000]',
        ];

        if (!$this->validateData($data, $rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $id = $this->kontak->kirim($data['nama'], $data['email'], $data['subjek'], $data['pesan']);

        if ($id === false) {
            return $this->kembaliDenganError(
                $this->kontak->errors() ?: ['gagal' => 'Pesan gagal dikirim. Silakan coba lagi.']
            );
        }

        return redirect()->to('/kontak')->with('success', 'Pesan Anda berhasil dikirim. Terima kasih!');
    }

    /**
     * Maksimal 3 pesan per 10 menit per IP.
     */
    private function terlaluBanyakKirim(): bool
    {
        return \Config\Services::throttler()->check(md5($this->request->getIPAddress()), 3, 600) === false;
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}