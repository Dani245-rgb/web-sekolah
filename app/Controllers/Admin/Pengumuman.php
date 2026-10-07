<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\PengumumanService;
use InvalidArgumentException;
use Throwable;

class Pengumuman extends BaseController
{
    protected PengumumanService $pengumumanService;

    public function __construct()
    {
        $this->pengumumanService = service('pengumumanService');
    }

    public function index()
    {
        $data['pengumuman'] = $this->pengumumanService->daftar();

        return view('admin/pengumuman/index', $data);
    }

    public function create()
    {
        $data['pengumuman'] = null;

        return view('admin/pengumuman/form', $data);
    }

    public function store()
    {
        try {
            $this->pengumumanService->tambah($this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Pengumuman gagal ditambahkan.', true);
        }

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit($id)
    {
        try {
            $data['pengumuman'] = $this->pengumumanService->ambil((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Data pengumuman gagal dimuat.');
        }

        return view('admin/pengumuman/form', $data);
    }

    public function update($id)
    {
        try {
            $this->pengumumanService->ubah((int) $id, $this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Pengumuman gagal diperbarui.', true);
        }

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->pengumumanService->hapus((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Pengumuman gagal dihapus.');
        }

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function inputForm(): array
    {
        return [
            'judul'           => $this->request->getPost('judul'),
            'isi'             => $this->request->getPost('isi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish'),
            'status'          => $this->request->getPost('status'),
        ];
    }

    /**
     * InvalidArgumentException = pesan aman, tampilkan apa adanya.
     * Exception lain = error sistem, log lalu tampilkan pesan umum.
     * Data tidak ditemukan dan kegagalan non-form kembali ke daftar (flash "error").
     * Kegagalan form kembali ke form dengan input terisi (flash "errors").
     */
    private function gagal(Throwable $e, string $pesanUmum, bool $form = false)
    {
        if ($e instanceof InvalidArgumentException) {
            $pesan = $e->getMessage();
        } else {
            log_message('error', '[Pengumuman] ' . $e->getMessage());
            $pesan = $pesanUmum;
        }

        if (!$form || $pesan === PengumumanService::TIDAK_DITEMUKAN) {
            return redirect()->to('/admin/pengumuman')->with('error', $pesan);
        }

        return redirect()->back()->withInput()->with('errors', ['pengumuman' => $pesan]);
    }
}