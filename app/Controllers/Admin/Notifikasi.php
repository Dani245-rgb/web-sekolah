<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\NotifikasiService;
use InvalidArgumentException;
use Throwable;

class Notifikasi extends BaseController
{
    protected NotifikasiService $notifikasiService;

    public function __construct()
    {
        $this->notifikasiService = service('notifikasiService');
    }

    public function index()
    {
        $data['notifikasi'] = $this->notifikasiService->daftar($this->idUser(), 50);

        return view('admin/notifikasi/index', $data);
    }

    /**
     * Dipanggil AJAX tiap beberapa detik dari topbar.
     */
    public function poll()
    {
        try {
            return $this->response->setJSON($this->notifikasiService->ringkasan($this->idUser()));
        } catch (Throwable $e) {
            log_message('error', '[Notifikasi] ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON(['error' => 'Notifikasi gagal dimuat.']);
        }
    }

    public function baca($idNotif)
    {
        try {
            $link = $this->notifikasiService->baca((int) $idNotif, $this->idUser());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Notifikasi gagal dibuka.');
        }

        return redirect()->to($link ?? '/admin/notifikasi');
    }

    public function bacaSemua()
    {
        try {
            $this->notifikasiService->bacaSemua($this->idUser());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Notifikasi gagal ditandai dibaca.');
        }

        return redirect()->back();
    }

    private function idUser(): int
    {
        return (int) session()->get('id_user');
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
            log_message('error', '[Notifikasi] ' . $e->getMessage());
            $pesan = $pesanUmum;
        }

        return redirect()->to('/admin/notifikasi')->with('error', $pesan);
    }
}