<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BeritaModel;
use App\Models\AuditLogModel;

class Berita extends BaseController
{
    protected BeritaModel $beritaModel;
    protected AuditLogModel $auditLogModel;

    public function __construct()
    {
        $this->beritaModel   = new BeritaModel();
        $this->auditLogModel = new AuditLogModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('q');

        $builder = $this->beritaModel->orderBy('created_at', 'DESC');
        if ($keyword) {
            $builder->like('judul', $keyword);
        }

        $beritaList = $builder->paginate(10);

        return view('admin/berita/index', [
            'beritaList' => $beritaList,
            'pager'      => $this->beritaModel->pager,
            'keyword'    => $keyword,
        ]);
    }

    public function create()
    {
        return view('admin/berita/form', ['berita' => null]);
    }

    public function store()
    {
        $rules = [
            'judul'    => 'required|min_length[5]|max_length[200]',
            'kategori' => 'required|in_list[Akademik,Prestasi,Kegiatan,Umum]',
            'konten'   => 'required',
            'status'   => 'required|in_list[Draft,Published]',
            'posisi'   => 'required|in_list[hero,utama,biasa,populer,hits]',
            'gambar'   => 'permit_empty|is_image[gambar]|max_size[gambar,2048]|mime_in[gambar,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = $this->request->getPost('judul');
        $slug  = $this->beritaModel->generateUniqueSlug($judul);

        $namaFile = null;
        $file = $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $namaFile = $file->getRandomName();
            $file->move(FCPATH . 'assets/images/berita', $namaFile);
        }

        $this->beritaModel->insert([
            'judul'           => $judul,
            'slug'            => $slug,
            'kategori'        => $this->request->getPost('kategori'),
            'gambar'          => $namaFile,
            'ringkasan'       => $this->request->getPost('ringkasan'),
            'konten'          => $this->request->getPost('konten'),
            'status'          => $this->request->getPost('status'),
            'posisi'          => $this->request->getPost('posisi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish') ?: date('Y-m-d'),
            'id_user'         => session()->get('id_user'),
        ]);

        $this->auditLogModel->catat(session()->get('id_user'), session()->get('nama') ?? 'Admin', 'Tambah Berita', "Judul:{$judul}");

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $berita = $this->beritaModel->find($id);
        if (!$berita) {
            return redirect()->to('/admin/berita')->with('errors', ['404' => 'Berita tidak ditemukan.']);
        }

        return view('admin/berita/form', ['berita' => $berita]);
    }

    public function update($id)
    {
        $berita = $this->beritaModel->find($id);
        if (!$berita) {
            return redirect()->to('/admin/berita')->with('errors', ['404' => 'Berita tidak ditemukan.']);
        }

        $rules = [
            'judul'    => 'required|min_length[5]|max_length[200]',
            'kategori' => 'required|in_list[Akademik,Prestasi,Kegiatan,Umum]',
            'konten'   => 'required',
            'status'   => 'required|in_list[Draft,Published]',
            'posisi'   => 'required|in_list[hero,utama,biasa,populer,hits]',
            'gambar'   => 'permit_empty|is_image[gambar]|max_size[gambar,2048]|mime_in[gambar,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = $this->request->getPost('judul');
        $slug  = $judul !== $berita['judul']
            ? $this->beritaModel->generateUniqueSlug($judul, $id)
            : $berita['slug'];

        $namaFile = $berita['gambar'];
        $file = $this->request->getFile('gambar');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if ($namaFile && file_exists(FCPATH . 'assets/images/berita/' . $namaFile)) {
                unlink(FCPATH . 'assets/images/berita/' . $namaFile);
            }
            $namaFile = $file->getRandomName();
            $file->move(FCPATH . 'assets/images/berita', $namaFile);
        }

        $this->beritaModel->update($id, [
            'judul'           => $judul,
            'slug'            => $slug,
            'kategori'        => $this->request->getPost('kategori'),
            'gambar'          => $namaFile,
            'ringkasan'       => $this->request->getPost('ringkasan'),
            'konten'          => $this->request->getPost('konten'),
            'status'          => $this->request->getPost('status'),
            'posisi'          => $this->request->getPost('posisi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish') ?: $berita['tanggal_publish'],
        ]);

        $this->auditLogModel->catat(session()->get('id_user'), session()->get('nama') ?? 'Admin', 'Edit Berita', "Judul:{$judul}");

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil diperbarui.');
    }

    public function delete($id)
    {
        $berita = $this->beritaModel->find($id);
        if (!$berita) {
            return redirect()->to('/admin/berita')->with('errors', ['404' => 'Berita tidak ditemukan.']);
        }

        if ($berita['gambar'] && file_exists(FCPATH . 'assets/images/berita/' . $berita['gambar'])) {
            unlink(FCPATH . 'assets/images/berita/' . $berita['gambar']);
        }

        $this->beritaModel->delete($id);

        $this->auditLogModel->catat(session()->get('id_user'), session()->get('nama') ?? 'Admin', 'Hapus Berita', "Judul:{$berita['judul']}");

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil dihapus.');
    }
}
