<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\PklService;
use InvalidArgumentException;
use Throwable;

class Pkl extends BaseController
{
    protected PklService $pkl;

    public function __construct()
    {
        $this->pkl = service('pklService');
    }

    public function index()
    {
        return view('admin/pkl/index', $this->pkl->daftar(
            $this->request->getGet('keyword'),
            $this->request->getGet('status')
        ));
    }

    public function create()
    {
        return view('admin/pkl/form', $this->pkl->dataForm(null));
    }

    public function store()
    {
        try {
            $this->pkl->tambah($this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan penempatan PKL. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $penempatan = $this->pkl->cari($id);
        if (!$penempatan) {
            return $this->tidakDitemukan();
        }

        return view('admin/pkl/form', $this->pkl->dataForm($penempatan));
    }

    public function update($id)
    {
        if (!$this->pkl->cari($id)) {
            return $this->tidakDitemukan();
        }

        try {
            $this->pkl->ubah((int) $id, $this->inputForm());
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui penempatan PKL. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->pkl->hapus((int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus penempatan PKL: ' . $e->getMessage());

            if ($e instanceof InvalidArgumentException) {
                return $this->tidakDitemukan();
            }

            return redirect()->to('/admin/pkl')->with('errors', ['pkl' => 'Gagal menghapus penempatan PKL. Silakan coba lagi.']);
        }

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost([
            'id_siswa', 'id_guru_pembimbing', 'nama_perusahaan', 'alamat_perusahaan',
            'nama_pembimbing_industri', 'no_hp_pembimbing_industri',
            'tanggal_mulai', 'tanggal_selesai', 'status', 'catatan',
        ]);
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/pkl')->with('errors', ['404' => 'Data penempatan tidak ditemukan.']);
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} penempatan PKL: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return redirect()->back()->withInput()->with('errors', ['pkl' => $pesan]);
    }
}