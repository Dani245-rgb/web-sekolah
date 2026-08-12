<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PrestasiModel;

class Prestasi extends BaseController
{
    protected $prestasiModel;

    public function __construct()
    {
        $this->prestasiModel = new PrestasiModel();
    }

    public function index()
    {
        $data['prestasi'] = $this->prestasiModel->orderBy('tanggal', 'DESC')->findAll();
        return view('admin/prestasi/index', $data);
    }

    public function create()
    {
        $data['prestasi'] = null;
        return view('admin/prestasi/form', $data);
    }

    public function store()
    {
        $rules = [
            'judul'   => 'required|min_length[3]|max_length[255]',
            'tingkat' => 'required|in_list[Kabupaten,Provinsi,Nasional,Internasional]',
            'tanggal' => 'required|valid_date',
            'status'  => 'required|in_list[Published,Draft]',
            'foto'    => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file    = $this->request->getFile('foto');
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/prestasi', $newName);

        $this->prestasiModel->insert([
            'judul'   => $this->request->getPost('judul'),
            'tim'     => $this->request->getPost('tim'),
            'tingkat' => $this->request->getPost('tingkat'),
            'foto'    => $newName,
            'tanggal' => $this->request->getPost('tanggal'),
            'status'  => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/prestasi')->with('success', 'Prestasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['prestasi'] = $this->prestasiModel->find($id);

        if (!$data['prestasi']) {
            return redirect()->to('/admin/prestasi')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/prestasi/form', $data);
    }

    public function update($id)
    {
        $prestasi = $this->prestasiModel->find($id);
        if (!$prestasi) {
            return redirect()->to('/admin/prestasi')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'judul'   => 'required|min_length[3]|max_length[255]',
            'tingkat' => 'required|in_list[Kabupaten,Provinsi,Nasional,Internasional]',
            'tanggal' => 'required|valid_date',
            'status'  => 'required|in_list[Published,Draft]',
        ];

        if ($this->request->getFile('foto')->isValid()) {
            $rules['foto'] = 'is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'judul'   => $this->request->getPost('judul'),
            'tim'     => $this->request->getPost('tim'),
            'tingkat' => $this->request->getPost('tingkat'),
            'tanggal' => $this->request->getPost('tanggal'),
            'status'  => $this->request->getPost('status'),
        ];

        $file = $this->request->getFile('foto');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/prestasi', $newName);
            $updateData['foto'] = $newName;

            $oldPath = FCPATH . 'uploads/prestasi/' . $prestasi['foto'];
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $this->prestasiModel->update($id, $updateData);

        return redirect()->to('/admin/prestasi')->with('success', 'Prestasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $prestasi = $this->prestasiModel->find($id);
        if (!$prestasi) {
            return redirect()->to('/admin/prestasi')->with('error', 'Data tidak ditemukan.');
        }

        $path = FCPATH . 'uploads/prestasi/' . $prestasi['foto'];
        if (is_file($path)) {
            unlink($path);
        }

        $this->prestasiModel->delete($id);

        return redirect()->to('/admin/prestasi')->with('success', 'Prestasi berhasil dihapus.');
    }
}