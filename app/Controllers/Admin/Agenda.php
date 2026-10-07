<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AgendaService;
use InvalidArgumentException;
use Throwable;

class Agenda extends BaseController
{
    protected AgendaService $agendaService;

    public function __construct()
    {
        $this->agendaService = service('agendaService');
    }

    public function index()
    {
        $data['agenda'] = $this->agendaService->daftar();

        return view('admin/agenda/index', $data);
    }

    public function create()
    {
        $data['agenda'] = null;

        return view('admin/agenda/form', $data);
    }

    public function store()
    {
        try {
            $this->agendaService->tambah($this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Agenda gagal ditambahkan.', true);
        }

        return redirect()->to('/admin/agenda')->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit($id)
    {
        try {
            $data['agenda'] = $this->agendaService->ambil((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Data agenda gagal dimuat.');
        }

        return view('admin/agenda/form', $data);
    }

    public function update($id)
    {
        try {
            $this->agendaService->ubah((int) $id, $this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Agenda gagal diperbarui.', true);
        }

        return redirect()->to('/admin/agenda')->with('success', 'Agenda berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->agendaService->hapus((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Agenda gagal dihapus.');
        }

        return redirect()->to('/admin/agenda')->with('success', 'Agenda berhasil dihapus.');
    }

    private function inputForm(): array
    {
        return [
            'judul'   => $this->request->getPost('judul'),
            'tanggal' => $this->request->getPost('tanggal'),
            'waktu'   => $this->request->getPost('waktu'),
            'lokasi'  => $this->request->getPost('lokasi'),
            'status'  => $this->request->getPost('status'),
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
            log_message('error', '[Agenda] ' . $e->getMessage());
            $pesan = $pesanUmum;
        }

        if (!$form || $pesan === AgendaService::TIDAK_DITEMUKAN) {
            return redirect()->to('/admin/agenda')->with('error', $pesan);
        }

        return redirect()->back()->withInput()->with('errors', ['agenda' => $pesan]);
    }
}