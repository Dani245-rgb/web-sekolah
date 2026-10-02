<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\BeritaService;
use InvalidArgumentException;
use Throwable;

class Berita extends BaseController
{
    protected BeritaService $berita;

    public function __construct()
    {
        $this->berita = service('beritaService');
    }

    public function index()
    {
        $keyword = $this->request->getGet('q');
        $hasil   = $this->berita->daftar($keyword);

        return view('admin/berita/index', [
            'beritaList' => $hasil['berita'],
            'pager'      => $hasil['pager'],
            'keyword'    => $keyword,
        ]);
    }

    public function create()
    {
        return view('admin/berita/form', ['berita' => null]);
    }

    public function store()
    {
        if (!$this->validate($this->aturanValidasi())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->berita->tambah(
                $this->inputForm(),
                $this->request->getFile('gambar'),
                session()->get('id_user'),
                $this->namaUser()
            );
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan berita. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $berita = $this->berita->cari($id);
        if (!$berita) {
            return $this->tidakDitemukan();
        }

        return view('admin/berita/form', ['berita' => $berita]);
    }

    public function update($id)
    {
        if (!$this->berita->cari($id)) {
            return $this->tidakDitemukan();
        }

        if (!$this->validate($this->aturanValidasi())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->berita->ubah(
                (int) $id,
                $this->inputForm(),
                $this->request->getFile('gambar'),
                session()->get('id_user'),
                $this->namaUser()
            );
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui berita. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $this->berita->hapus((int) $id, session()->get('id_user'), $this->namaUser());
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Berita: ' . $e->getMessage());

            $pesan = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal menghapus berita. Silakan coba lagi.';

            return redirect()->to('/admin/berita')->with('errors', ['berita' => $pesan]);
        }

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost([
            'judul', 'kategori', 'ringkasan', 'konten', 'status', 'posisi', 'tanggal_publish',
        ]);
    }

    private function namaUser(): string
    {
        return session()->get('nama') ?? 'Admin';
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/berita')->with('errors', ['404' => 'Berita tidak ditemukan.']);
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Berita: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return redirect()->back()->withInput()->with('errors', ['berita' => $pesan]);
    }

    /**
     * Validasi format input. Gambar tidak divalidasi di sini:
     * tipe dan ukuran dicek di FileUploadService.
     */
    private function aturanValidasi(): array
    {
        return [
            'judul'    => 'required|min_length[5]|max_length[200]',
            'kategori' => 'required|in_list[Akademik,Prestasi,Kegiatan,Umum]',
            'konten'   => 'required',
            'status'   => 'required|in_list[Draft,Published]',
            'posisi'   => 'required|in_list[hero,utama,biasa,populer,hits]',
        ];
    }
}