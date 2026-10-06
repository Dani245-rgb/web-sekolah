<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\PpdbAdminService;
use InvalidArgumentException;
use Throwable;

class PpdbAdmin extends BaseController
{
    protected PpdbAdminService $ppdbAdminService;

    public function __construct()
    {
        $this->ppdbAdminService = service('ppdbAdminService');
    }

    public function index()
    {
        $data['pendaftar'] = $this->ppdbAdminService->daftar();

        return view('admin/ppdb/index', $data);
    }

    public function detail($id)
    {
        try {
            $data['pendaftar'] = $this->ppdbAdminService->ambil((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Data pendaftar gagal dimuat.', '/admin/ppdb');
        }

        return view('admin/ppdb/detail', $data);
    }

    public function updateStatus($id)
    {
        try {
            $this->ppdbAdminService->ubahStatus((int) $id, (string) $this->request->getPost('status'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Status gagal disimpan.');
        }

        return redirect()->to('/admin/ppdb/detail/' . $id)->with('success', 'Status pendaftar berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->ppdbAdminService->hapus((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Data pendaftar gagal dihapus.', '/admin/ppdb');
        }

        return redirect()->to('/admin/ppdb')->with('success', 'Data pendaftar berhasil dihapus.');
    }

    public function pengaturan()
    {
        $data['setting'] = $this->ppdbAdminService->pengaturan();

        return view('admin/ppdb/pengaturan', $data);
    }

    public function updatePengaturan()
    {
        try {
            $this->ppdbAdminService->simpanPengaturan([
                'status'       => $this->request->getPost('status'),
                'pesan_tutup'  => $this->request->getPost('pesan_tutup'),
                'tahun_ajaran' => $this->request->getPost('tahun_ajaran'),
            ]);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Pengaturan PPDB gagal disimpan.', null, true);
        }

        return redirect()->to('/admin/ppdb/pengaturan')->with('success', 'Pengaturan PPDB berhasil diperbarui.');
    }

    /**
     * InvalidArgumentException = pesan aman, tampilkan apa adanya.
     * Exception lain = error sistem, log lalu tampilkan pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, ?string $ke = null, bool $denganInput = false)
    {
        if ($e instanceof InvalidArgumentException) {
            $pesan = $e->getMessage();
        } else {
            log_message('error', '[PpdbAdmin] ' . $e->getMessage());
            $pesan = $pesanUmum;
        }

        $redirect = $ke !== null ? redirect()->to($ke) : redirect()->back();

        if ($denganInput) {
            $redirect = $redirect->withInput();
        }

        return $redirect->with('error', $pesan);
    }
}