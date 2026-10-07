<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\KontakAdminService;
use InvalidArgumentException;
use Throwable;

class KontakAdmin extends BaseController
{
    protected KontakAdminService $kontakAdminService;

    public function __construct()
    {
        $this->kontakAdminService = service('kontakAdminService');
    }

    public function index()
    {
        $data['pesan'] = $this->kontakAdminService->daftar();

        return view('admin/kontak/index', $data);
    }

    public function detail($id)
    {
        try {
            $data['pesan'] = $this->kontakAdminService->lihat((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Pesan gagal dimuat.');
        }

        return view('admin/kontak/detail', $data);
    }

    public function delete($id)
    {
        try {
            $this->kontakAdminService->hapus((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Pesan gagal dihapus.');
        }

        return redirect()->to('/admin/kontak')->with('success', 'Pesan berhasil dihapus.');
    }

    /**
     * InvalidArgumentException = pesan aman, tampilkan apa adanya.
     * Exception lain = error sistem, log lalu tampilkan pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum)
    {
        if ($e instanceof InvalidArgumentException) {
            $pesan = $e->getMessage();
        } else {
            log_message('error', '[KontakAdmin] ' . $e->getMessage());
            $pesan = $pesanUmum;
        }

        return redirect()->to('/admin/kontak')->with('error', $pesan);
    }
}