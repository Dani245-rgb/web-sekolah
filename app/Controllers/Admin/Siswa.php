<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SiswaService;
use Throwable;

class Siswa extends BaseController
{
    protected SiswaService $siswa;

    public function __construct()
    {
        $this->siswa = service('siswaService');
    }

    public function index()
    {
        $keyword = $this->request->getGet('cari');
        $hasil   = $this->siswa->daftar($keyword, $this->request->getGet('jurusan'));

        return view('admin/siswa/index', [
            'siswa'        => $hasil['siswa'],
            'pager'        => $hasil['pager'],
            'keyword'      => $keyword,
            'jurusanList'  => $this->siswa->daftarJurusanAktif(),
            'jurusanAktif' => $hasil['jurusanAktif'],
        ]);
    }

    public function create()
    {
        return view('admin/siswa/create', ['jurusanList' => $this->siswa->daftarJurusanAktif()]);
    }

    public function store()
    {
        if (!$this->validate($this->aturanStore())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hasil = $this->aman(
            fn () => $this->siswa->tambah($this->inputForm(), $this->request->getFile('foto')),
            'simpan siswa'
        );

        if (!$hasil['sukses']) {
            return redirect()->back()->withInput()->with('errors', ['db' => $hasil['pesan']]);
        }

        return redirect()->to('/admin/siswa')
            ->with('success', "Siswa {$this->request->getPost('nama')} berhasil ditambahkan.");
    }

    public function edit($id_siswa)
    {
        $siswaData = $this->siswa->cari($id_siswa);

        if (!$siswaData) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

        return view('admin/siswa/edit', [
            'siswaData'   => $siswaData,
            'jurusanList' => $this->siswa->daftarJurusanAktif(),
        ]);
    }

    public function update($id_siswa)
    {
        if (!$this->siswa->cari($id_siswa)) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

        if (!$this->validate($this->aturanUpdate((int) $id_siswa))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $hasil = $this->aman(
            fn () => $this->siswa->ubah((int) $id_siswa, $this->inputForm(), $this->request->getFile('foto')),
            'update siswa'
        );

        if (!$hasil['sukses']) {
            return redirect()->back()->withInput()->with('errors', ['update' => $hasil['pesan']]);
        }

        return redirect()->to('/admin/siswa')->with('success', $hasil['pesan']);
    }

    public function delete($id_siswa)
    {
        return $this->balas($this->siswa->hapus((int) $id_siswa), '/admin/siswa');
    }

    public function trash()
    {
        return view('admin/siswa/trash', ['siswaTerhapus' => $this->siswa->daftarTerhapus()]);
    }

    public function restore($id_siswa)
    {
        return $this->balas($this->siswa->pulihkan((int) $id_siswa), '/admin/siswa/trash');
    }

    public function forceDelete($id_siswa)
    {
        return $this->balas($this->siswa->hapusPermanen((int) $id_siswa), '/admin/siswa/trash');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost([
            'nis', 'nisn', 'jurusan_id', 'nama', 'tempat_lahir', 'tanggal_lahir',
            'jenis_kelamin', 'agama', 'alamat', 'nama_ayah', 'nama_ibu',
            'pekerjaan_ortu', 'no_hp_ortu', 'email',
        ]);
    }

    /**
     * Jalankan aksi service; exception tak terduga dicatat dan diubah jadi pesan umum.
     */
    private function aman(callable $aksi, string $konteks): array
    {
        try {
            return $aksi();
        } catch (Throwable $e) {
            log_message('error', "Gagal {$konteks}: " . $e->getMessage());

            return ['sukses' => false, 'pesan' => 'Terjadi kesalahan. Silakan coba lagi.'];
        }
    }

    private function balas(array $hasil, string $url)
    {
        $redirect = redirect()->to($url);

        return $hasil['sukses']
            ? $redirect->with('success', $hasil['pesan'])
            : $redirect->with('errors', ['db' => $hasil['pesan']]);
    }

    private function aturanStore(): array
    {
        return [
            'nis'           => 'required|max_length[20]|is_unique[siswa.nis]',
            'nisn'          => 'required|max_length[20]|is_unique[siswa.nisn]',
            'nama'          => 'required|min_length[3]|max_length[100]',
            'tanggal_lahir' => 'required|valid_date',
            'jurusan_id'    => 'permit_empty|is_not_unique[jurusan.id_jurusan]',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'email'         => 'permit_empty|valid_email',
            'foto'          => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];
    }

    private function aturanUpdate(int $id): array
    {
        return [
            'nis'           => "required|max_length[20]|is_unique[siswa.nis,id_siswa,{$id}]",
            'nisn'          => "required|max_length[20]|is_unique[siswa.nisn,id_siswa,{$id}]",
            'jurusan_id'    => 'permit_empty|is_not_unique[jurusan.id_jurusan]',
            'nama'          => 'required|min_length[3]|max_length[100]',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'email'         => 'permit_empty|valid_email',
            'foto'          => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];
    }
}