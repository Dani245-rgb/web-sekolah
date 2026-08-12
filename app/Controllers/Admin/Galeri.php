<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GaleriModel;

class Galeri extends BaseController
{
    protected $galeriModel;

    public function __construct()
    {
        $this->galeriModel = new GaleriModel();
    }

    public function index()
    {
        $data['galeri'] = $this->galeriModel->orderBy('created_at', 'DESC')->findAll();
        return view('admin/galeri/index', $data);
    }

    public function create()
    {
        $data['galeri'] = null;
        return view('admin/galeri/form', $data);
    }

    public function store()
    {
        $rules = [
            'judul'    => 'required|min_length[3]|max_length[255]',
            'kategori' => 'required|in_list[Kegiatan,Fasilitas,Prestasi,Lainnya]',
            'status'   => 'required|in_list[Published,Draft]',
            'foto'     => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('foto');
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/galeri', $newName);

        $this->galeriModel->insert([
            'judul'     => $this->request->getPost('judul'),
            'foto'      => $newName,
            'kategori'  => $this->request->getPost('kategori'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['galeri'] = $this->galeriModel->find($id);

        if (!$data['galeri']) {
            return redirect()->to('/admin/galeri')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/galeri/form', $data);
    }

    public function update($id)
    {
        $galeri = $this->galeriModel->find($id);
        if (!$galeri) {
            return redirect()->to('/admin/galeri')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'judul'    => 'required|min_length[3]|max_length[255]',
            'kategori' => 'required|in_list[Kegiatan,Fasilitas,Prestasi,Lainnya]',
            'status'   => 'required|in_list[Published,Draft]',
        ];

        // foto hanya divalidasi kalau user upload foto baru
        if ($this->request->getFile('foto')->isValid()) {
            $rules['foto'] = 'is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'judul'     => $this->request->getPost('judul'),
            'kategori'  => $this->request->getPost('kategori'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ];

        $file = $this->request->getFile('foto');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/galeri', $newName);
            $updateData['foto'] = $newName;

            // hapus foto lama
            $oldPath = FCPATH . 'uploads/galeri/' . $galeri['foto'];
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $this->galeriModel->update($id, $updateData);

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil diperbarui.');
    }

    public function delete($id)
    {
        $galeri = $this->galeriModel->find($id);
        if (!$galeri) {
            return redirect()->to('/admin/galeri')->with('error', 'Data tidak ditemukan.');
        }

        $path = FCPATH . 'uploads/galeri/' . $galeri['foto'];
        if (is_file($path)) {
            unlink($path);
        }

        $this->galeriModel->delete($id);

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil dihapus.');
    }
}