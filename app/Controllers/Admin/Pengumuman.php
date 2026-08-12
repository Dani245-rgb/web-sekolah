<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengumumanModel;

class Pengumuman extends BaseController
{
    protected $pengumumanModel;

    public function __construct()
    {
        $this->pengumumanModel = new PengumumanModel();
    }

    public function index()
    {
        $data['pengumuman'] = $this->pengumumanModel->orderBy('tanggal_publish', 'DESC')->findAll();
        return view('admin/pengumuman/index', $data);
    }

    public function create()
    {
        $data['pengumuman'] = null;
        return view('admin/pengumuman/form', $data);
    }

    public function store()
    {
        $rules = [
            'judul'           => 'required|min_length[3]|max_length[255]',
            'isi'             => 'required',
            'tanggal_publish' => 'required|valid_date',
            'status'          => 'required|in_list[Published,Draft]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = $this->request->getPost('judul');
        $slug  = $this->pengumumanModel->generateUniqueSlug($judul);

        $this->pengumumanModel->insert([
            'judul'           => $judul,
            'slug'            => $slug,
            'isi'             => $this->request->getPost('isi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish'),
            'status'          => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['pengumuman'] = $this->pengumumanModel->find($id);

        if (!$data['pengumuman']) {
            return redirect()->to('/admin/pengumuman')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/pengumuman/form', $data);
    }

    public function update($id)
    {
        $pengumuman = $this->pengumumanModel->find($id);
        if (!$pengumuman) {
            return redirect()->to('/admin/pengumuman')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'judul'           => 'required|min_length[3]|max_length[255]',
            'isi'             => 'required',
            'tanggal_publish' => 'required|valid_date',
            'status'          => 'required|in_list[Published,Draft]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = $this->request->getPost('judul');
        $slug  = $this->pengumumanModel->generateUniqueSlug($judul, $id);

        $this->pengumumanModel->update($id, [
            'judul'           => $judul,
            'slug'            => $slug,
            'isi'             => $this->request->getPost('isi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish'),
            'status'          => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function delete($id)
    {
        $pengumuman = $this->pengumumanModel->find($id);
        if (!$pengumuman) {
            return redirect()->to('/admin/pengumuman')->with('error', 'Data tidak ditemukan.');
        }

        $this->pengumumanModel->delete($id);

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil dihapus.');
    }
}