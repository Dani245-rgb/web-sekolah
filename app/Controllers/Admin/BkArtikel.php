<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BkArtikelModel;
use App\Services\BkArtikelService;
use InvalidArgumentException;
use Throwable;

class BkArtikel extends BaseController
{
    protected BkArtikelService $bk;

    public function __construct()
    {
        $this->bk = service('bkArtikelService');
    }

    public function index()
    {
        $kategoriFilter = $this->request->getGet('kategori');

        return view('admin/bk_artikel/index', [
            'artikelList'    => $this->bk->daftar($kategoriFilter),
            'kategoriFilter' => $kategoriFilter,
        ]);
    }

    public function create()
    {
        return view('admin/bk_artikel/create');
    }

    public function store()
    {
        if (!$this->validate($this->baseRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->bk->tambah($this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan data. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/bk-artikel')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->bk->cari($id);
        if ($item === null) {
            return $this->tidakDitemukan();
        }

        return view('admin/bk_artikel/edit', ['item' => $item]);
    }

    public function update($id)
    {
        if ($this->bk->cari($id) === null) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->baseRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->bk->ubah((int) $id, $this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui data. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/bk-artikel')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($this->bk->cari($id) === null) {
            return $this->tidakDitemukan();
        }

        try {
            $this->bk->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Artikel BK: ' . $e->getMessage());
            return redirect()->to('/admin/bk-artikel')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/bk-artikel')->with('success', 'Artikel berhasil dihapus.');
    }

    private function inputForm(): array
    {
        return $this->request->getPost(['kategori', 'judul', 'konten', 'link_eksternal', 'deadline', 'status']);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/bk-artikel')->with('error', 'Artikel tidak ditemukan.');
    }

    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Artikel BK: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return redirect()->back()->withInput()->with('error', $pesan);
    }

    /**
     * Validasi format input. Aturan bisnis ada di BkArtikelService.
     * Foto tidak divalidasi di sini: tipe dan ukuran dicek di FileUploadService.
     */
    private function baseRules(): array
    {
        $kategori = implode(',', array_keys(BkArtikelModel::KATEGORI));

        return [
            'kategori'       => "required|in_list[{$kategori}]",
            'judul'          => 'required|max_length[255]',
            'konten'         => 'permit_empty|max_length[20000]',
            'link_eksternal' => 'permit_empty|valid_url_strict|max_length[255]',
            'deadline'       => 'permit_empty|valid_date',
            'status'         => 'permit_empty|in_list[Draft,Published]',
        ];
    }
}