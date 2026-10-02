<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\UnduhanService;
use InvalidArgumentException;
use Throwable;

class Unduhan extends BaseController
{
    protected UnduhanService $unduhan;

    public function __construct()
    {
        $this->unduhan = service('unduhanService');
    }

    public function index()
    {
        $kategoriFilter = $this->request->getGet('kategori');

        return view('admin/unduhan/index', [
            'unduhanList'    => $this->unduhan->daftar($kategoriFilter),
            'kategoriList'   => $this->unduhan->daftarKategori(),
            'kategoriFilter' => $kategoriFilter,
        ]);
    }

    public function create()
    {
        return view('admin/unduhan/create', ['kategoriList' => $this->unduhan->daftarKategori()]);
    }

    public function store()
    {
        $rules         = $this->baseRules();
        $rules['file'] = 'uploaded[file]';

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->unduhan->tambah($this->inputForm(), $this->request->getFile('file'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan data. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/unduhan')->with('success', 'File berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->unduhan->cari($id);
        if ($item === null) {
            return $this->tidakDitemukan();
        }

        return view('admin/unduhan/edit', [
            'item'         => $item,
            'kategoriList' => $this->unduhan->daftarKategori(),
        ]);
    }

    public function update($id)
    {
        if ($this->unduhan->cari($id) === null) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->baseRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->unduhan->ubah((int) $id, $this->inputForm(), $this->request->getFile('file'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui data. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/unduhan')->with('success', 'File berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($this->unduhan->cari($id) === null) {
            return $this->tidakDitemukan();
        }

        try {
            $this->unduhan->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Unduhan: ' . $e->getMessage());
            return redirect()->to('/admin/unduhan')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/unduhan')->with('success', 'File berhasil dihapus.');
    }

    private function inputForm(): array
    {
        return $this->request->getPost(['kategori', 'judul', 'deskripsi', 'status']);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/unduhan')->with('error', 'Data tidak ditemukan.');
    }

    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Unduhan: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return redirect()->back()->withInput()->with('error', $pesan);
    }

    /**
     * Validasi format input. Aturan file (ekstensi, MIME, ukuran) ada di UnduhanService.
     */
    private function baseRules(): array
    {
        return [
            'kategori'  => 'required|max_length[100]',
            'judul'     => 'required|max_length[255]',
            'deskripsi' => 'permit_empty|max_length[2000]',
            'status'    => 'permit_empty|in_list[Draft,Published]',
        ];
    }
}