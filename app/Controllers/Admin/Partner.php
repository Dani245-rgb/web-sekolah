<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\PartnerService;
use InvalidArgumentException;
use Throwable;

class Partner extends BaseController
{
    protected PartnerService $partner;

    public function __construct()
    {
        $this->partner = service('partnerService');
    }

    public function index()
    {
        return view('admin/partner/index', ['partner' => $this->partner->daftar()]);
    }

    public function create()
    {
        return view('admin/partner/form', ['partner' => null]);
    }

    public function store()
    {
        $rules = $this->rules() + ['foto' => 'uploaded[foto]'];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->partner->tambah($this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan partner. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/partner')->with('success', 'Partner berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $partner = $this->partner->cari($id);

        if (!$partner) {
            return $this->tidakDitemukan();
        }

        return view('admin/partner/form', ['partner' => $partner]);
    }

    public function update($id)
    {
        if (!$this->partner->cari($id)) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->partner->ubah((int) $id, $this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui partner. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/partner')->with('success', 'Partner berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->partner->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Partner: ' . $e->getMessage());

            if ($e instanceof InvalidArgumentException) {
                return $this->tidakDitemukan();
            }

            return redirect()->to('/admin/partner')->with('error', 'Gagal menghapus partner. Silakan coba lagi.');
        }

        return redirect()->to('/admin/partner')->with('success', 'Partner berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost(['nama', 'deskripsi', 'status']);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/partner')->with('error', 'Data tidak ditemukan.');
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Partner: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return $this->kembaliDenganError(['partner' => $pesan]);
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }

    /**
     * Validasi format input. Foto tidak divalidasi di sini:
     * tipe dan ukuran dicek di FileUploadService.
     */
    private function rules(): array
    {
        return [
            'nama'      => 'required|min_length[3]|max_length[255]',
            'deskripsi' => 'required',
            'status'    => 'required|in_list[Published,Draft]',
        ];
    }
}