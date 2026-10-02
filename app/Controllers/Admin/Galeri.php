<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GaleriModel;
use App\Services\GaleriService;
use InvalidArgumentException;
use Throwable;

class Galeri extends BaseController
{
    protected GaleriService $galeri;

    public function __construct()
    {
        $this->galeri = service('galeriService');
    }

    public function index()
    {
        return view('admin/galeri/index', ['galeri' => $this->galeri->daftar()]);
    }

    public function create()
    {
        return view('admin/galeri/form', ['galeri' => null]);
    }

    public function store()
    {
        $rules = $this->rules() + ['foto' => 'uploaded[foto]'];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->galeri->tambah($this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan foto. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $galeri = $this->galeri->cari($id);

        if (!$galeri) {
            return $this->tidakDitemukan();
        }

        return view('admin/galeri/form', ['galeri' => $galeri]);
    }

    public function update($id)
    {
        if (!$this->galeri->cari($id)) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->galeri->ubah((int) $id, $this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui foto. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->galeri->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Galeri: ' . $e->getMessage());

            if ($e instanceof InvalidArgumentException) {
                return $this->tidakDitemukan();
            }

            return redirect()->to('/admin/galeri')->with('error', 'Gagal menghapus foto. Silakan coba lagi.');
        }

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost(['judul', 'kategori', 'deskripsi', 'status']);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/galeri')->with('error', 'Data tidak ditemukan.');
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Galeri: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return $this->kembaliDenganError(['galeri' => $pesan]);
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
        $kategori = implode(',', GaleriModel::KATEGORI);

        return [
            'judul'    => 'required|min_length[3]|max_length[255]',
            'kategori' => "required|in_list[{$kategori}]",
            'status'   => 'required|in_list[Published,Draft]',
        ];
    }
}