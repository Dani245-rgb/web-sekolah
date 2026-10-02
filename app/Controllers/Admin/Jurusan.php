<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\JurusanService;
use InvalidArgumentException;
use Throwable;

class Jurusan extends BaseController
{
    protected JurusanService $jurusan;

    public function __construct()
    {
        $this->jurusan = service('jurusanService');
    }

    public function index()
    {
        return view('admin/jurusan/index', ['jurusanList' => $this->jurusan->daftar()]);
    }

    public function create()
    {
        return view('admin/jurusan/create');
    }

    public function store()
    {
        if (!$this->validate($this->aturanValidasi())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->jurusan->tambah($this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan data. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->jurusan->cari($id);
        if ($item === null) {
            return $this->tidakDitemukan();
        }

        return view('admin/jurusan/edit', ['item' => $item]);
    }

    public function update($id)
    {
        if ($this->jurusan->cari($id) === null) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->aturanValidasi((int) $id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->jurusan->ubah((int) $id, $this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui data. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($this->jurusan->cari($id) === null) {
            return $this->tidakDitemukan();
        }

        try {
            $this->jurusan->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Jurusan: ' . $e->getMessage());
            return redirect()->to('/admin/jurusan')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil dihapus.');
    }

    private function inputForm(): array
    {
        return $this->request->getPost(['nama_jurusan', 'singkatan', 'deskripsi', 'kompetensi', 'prospek_kerja']);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/jurusan')->with('error', 'Jurusan tidak ditemukan.');
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Jurusan: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return redirect()->back()->withInput()->with('error', $pesan);
    }

    /**
     * Validasi format input (urusan HTTP). Aturan bisnis ada di JurusanService.
     * Foto tidak divalidasi di sini: tipe dan ukuran dicek di FileUploadService.
     */
    private function aturanValidasi(?int $excludeId = null): array
    {
        $uniqueNama = $excludeId === null
            ? 'is_unique[jurusan.nama_jurusan]'
            : "is_unique[jurusan.nama_jurusan,id_jurusan,{$excludeId}]";

        return [
            'nama_jurusan'  => "required|max_length[100]|{$uniqueNama}",
            'singkatan'     => 'permit_empty|max_length[20]',
            'deskripsi'     => 'permit_empty',
            'kompetensi'    => 'permit_empty',
            'prospek_kerja' => 'permit_empty',
        ];
    }
}