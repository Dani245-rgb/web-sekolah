<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\OrganisasiService;
use InvalidArgumentException;
use Throwable;

class Organisasi extends BaseController
{
    protected OrganisasiService $organisasi;

    public function __construct()
    {
        $this->organisasi = service('organisasiService');
    }

    public function index()
    {
        return view('admin/organisasi/index', ['organisasi' => $this->organisasi->daftar()]);
    }

    public function create()
    {
        return view('admin/organisasi/form', ['item' => null]);
    }

    public function store()
    {
        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->organisasi->tambah($this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan organisasi. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/organisasi')->with('success', 'Organisasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->organisasi->cari($id);

        if (!$item) {
            return $this->tidakDitemukan();
        }

        return view('admin/organisasi/form', ['item' => $item]);
    }

    public function update($id)
    {
        if (!$this->organisasi->cari($id)) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->organisasi->ubah((int) $id, $this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui organisasi. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/organisasi')->with('success', 'Organisasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->organisasi->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Organisasi: ' . $e->getMessage());

            if ($e instanceof InvalidArgumentException) {
                return $this->tidakDitemukan();
            }

            return redirect()->to('/admin/organisasi')->with('error', 'Gagal menghapus organisasi. Silakan coba lagi.');
        }

        return redirect()->to('/admin/organisasi')->with('success', 'Organisasi berhasil dihapus beserta anggotanya.');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost(['nama', 'deskripsi', 'status']);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/organisasi')->with('error', 'Data tidak ditemukan.');
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Organisasi: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return $this->kembaliDenganError(['organisasi' => $pesan]);
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }

    private function rules(): array
    {
        return [
            'nama'   => 'required|min_length[2]|max_length[100]',
            'status' => 'required|in_list[Published,Draft]',
        ];
    }
}