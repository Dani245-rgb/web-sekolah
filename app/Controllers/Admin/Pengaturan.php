<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\PengaturanService;
use InvalidArgumentException;
use Throwable;

class Pengaturan extends BaseController
{
    protected PengaturanService $pengaturan;

    public function __construct()
    {
        $this->pengaturan = service('pengaturanService');
    }

    public function index()
    {
        return view('admin/pengaturan/index', ['pengaturan' => $this->pengaturan->ambil()]);
    }

    public function update()
    {
        if (!$this->pengaturan->ambil()) {
            return redirect()->to('/admin/pengaturan')->with('error', 'Data pengaturan belum ada di database.');
        }

        $rules = [
            'nama_sekolah' => 'required|min_length[3]|max_length[150]',
            'email'        => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->pengaturan->simpan(
                $this->request->getPost(['nama_sekolah', 'alamat', 'telepon', 'email']),
                $this->request->getFile('logo'),
                $this->request->getFile('favicon'),
                session()->get('id_user'),
                session()->get('username')
            );
        } catch (Throwable $e) {
            log_message('error', 'Gagal simpan Pengaturan: ' . $e->getMessage());

            $pesan = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal menyimpan pengaturan. Silakan coba lagi.';

            return $this->kembaliDenganError(['pengaturan' => $pesan]);
        }

        return redirect()->to('/admin/pengaturan')->with('success', 'Pengaturan berhasil disimpan.');
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}