<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\EkstrakurikulerService;
use InvalidArgumentException;
use Throwable;

class Ekstrakurikuler extends BaseController
{
    protected EkstrakurikulerService $ekskul;

    public function __construct()
    {
        $this->ekskul = service('ekstrakurikulerService');
    }

    public function index()
    {
        return view('admin/ekstrakurikuler/index', ['ekskul' => $this->ekskul->daftar()]);
    }

    public function create()
    {
        return view('admin/ekstrakurikuler/form', ['ekskul' => null]);
    }

    public function store()
    {
        $rules = $this->rules() + ['foto' => 'uploaded[foto]'];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->ekskul->tambah($this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan ekstrakurikuler. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/ekstrakurikuler')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $ekskul = $this->ekskul->cari($id);

        if (!$ekskul) {
            return $this->tidakDitemukan();
        }

        return view('admin/ekstrakurikuler/form', ['ekskul' => $ekskul]);
    }

    public function update($id)
    {
        if (!$this->ekskul->cari($id)) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->ekskul->ubah((int) $id, $this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui ekstrakurikuler. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/ekstrakurikuler')->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->ekskul->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Ekstrakurikuler: ' . $e->getMessage());

            if ($e instanceof InvalidArgumentException) {
                return $this->tidakDitemukan();
            }

            return redirect()->to('/admin/ekstrakurikuler')->with('error', 'Gagal menghapus ekstrakurikuler. Silakan coba lagi.');
        }

        return redirect()->to('/admin/ekstrakurikuler')->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost(['nama', 'deskripsi', 'status']);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/ekstrakurikuler')->with('error', 'Data tidak ditemukan.');
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Ekstrakurikuler: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return $this->kembaliDenganError(['ekstrakurikuler' => $pesan]);
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
            'nama'   => 'required|min_length[3]|max_length[100]',
            'status' => 'required|in_list[Published,Draft]',
        ];
    }
}