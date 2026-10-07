<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\KalenderAkademikService;
use InvalidArgumentException;
use Throwable;

class KalenderAkademik extends BaseController
{
    protected KalenderAkademikService $kalenderService;

    public function __construct()
    {
        $this->kalenderService = service('kalenderAkademikService');
    }

    public function index()
    {
        $data['kalender'] = $this->kalenderService->daftar();

        return view('admin/kalender_akademik/index', $data);
    }

    public function create()
    {
        $data['item'] = null;

        return view('admin/kalender_akademik/form', $data);
    }

    public function store()
    {
        try {
            $this->kalenderService->tambah($this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Kegiatan gagal ditambahkan.', true);
        }

        return redirect()->to('/admin/kalender-akademik')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        try {
            $data['item'] = $this->kalenderService->ambil((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Data kegiatan gagal dimuat.');
        }

        return view('admin/kalender_akademik/form', $data);
    }

    public function update($id)
    {
        try {
            $this->kalenderService->ubah((int) $id, $this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Kegiatan gagal diperbarui.', true);
        }

        return redirect()->to('/admin/kalender-akademik')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->kalenderService->hapus((int) $id);
        } catch (Throwable $e) {
            return $this->gagal($e, 'Kegiatan gagal dihapus.');
        }

        return redirect()->to('/admin/kalender-akademik')->with('success', 'Kegiatan berhasil dihapus.');
    }

    private function inputForm(): array
    {
        return [
            'kegiatan'        => $this->request->getPost('kegiatan'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'keterangan'      => $this->request->getPost('keterangan'),
            'semester'        => $this->request->getPost('semester'),
            'tahun_ajaran'    => $this->request->getPost('tahun_ajaran'),
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
            log_message('error', '[KalenderAkademik] ' . $e->getMessage());
            $pesan = $pesanUmum;
        }

        if (!$form || $pesan === KalenderAkademikService::TIDAK_DITEMUKAN) {
            return redirect()->to('/admin/kalender-akademik')->with('error', $pesan);
        }

        return redirect()->back()->withInput()->with('errors', ['kalender' => $pesan]);
    }
}