<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\PrestasiService;
use InvalidArgumentException;
use Throwable;

class Prestasi extends BaseController
{
    protected PrestasiService $prestasi;

    public function __construct()
    {
        $this->prestasi = service('prestasiService');
    }

    public function index()
    {
        return view('admin/prestasi/index', ['prestasi' => $this->prestasi->daftar()]);
    }

    public function create()
    {
        return view('admin/prestasi/form', ['prestasi' => null]);
    }

    public function store()
    {
        $rules = $this->rules() + ['foto' => 'uploaded[foto]'];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->prestasi->tambah($this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan prestasi. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/prestasi')->with('success', 'Prestasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $prestasi = $this->prestasi->cari($id);

        if (!$prestasi) {
            return $this->tidakDitemukan();
        }

        return view('admin/prestasi/form', ['prestasi' => $prestasi]);
    }

    public function update($id)
    {
        if (!$this->prestasi->cari($id)) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->prestasi->ubah((int) $id, $this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui prestasi. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/prestasi')->with('success', 'Prestasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->prestasi->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Prestasi: ' . $e->getMessage());

            if ($e instanceof InvalidArgumentException) {
                return $this->tidakDitemukan();
            }

            return redirect()->to('/admin/prestasi')->with('error', 'Gagal menghapus prestasi. Silakan coba lagi.');
        }

        return redirect()->to('/admin/prestasi')->with('success', 'Prestasi berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost(['judul', 'tim', 'tingkat', 'tanggal', 'status']);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/prestasi')->with('error', 'Data tidak ditemukan.');
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Prestasi: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return $this->kembaliDenganError(['prestasi' => $pesan]);
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
            'judul'   => 'required|min_length[3]|max_length[255]',
            'tingkat' => 'required|in_list[Kabupaten,Provinsi,Nasional,Internasional]',
            'tanggal' => 'required|valid_date',
            'status'  => 'required|in_list[Published,Draft]',
        ];
    }
}