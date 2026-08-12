<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PartnerModel;

class Partner extends BaseController
{
    protected $partnerModel;

    public function __construct()
    {
        $this->partnerModel = new PartnerModel();
    }

    public function index()
    {
        $data['partner'] = $this->partnerModel->orderBy('nama', 'ASC')->findAll();
        return view('admin/partner/index', $data);
    }

    public function create()
    {
        $data['partner'] = null;
        return view('admin/partner/form', $data);
    }

    public function store()
    {
        $rules = [
            'nama'      => 'required|min_length[3]|max_length[255]',
            'deskripsi' => 'required',
            'status'    => 'required|in_list[Published,Draft]',
            'foto'      => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file    = $this->request->getFile('foto');
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/partner', $newName);

        $nama = $this->request->getPost('nama');
        $slug = $this->partnerModel->generateUniqueSlug($nama);

        $this->partnerModel->insert([
            'nama'      => $nama,
            'slug'      => $slug,
            'foto'      => $newName,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/partner')->with('success', 'Partner berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['partner'] = $this->partnerModel->find($id);

        if (!$data['partner']) {
            return redirect()->to('/admin/partner')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/partner/form', $data);
    }

    public function update($id)
    {
        $partner = $this->partnerModel->find($id);
        if (!$partner) {
            return redirect()->to('/admin/partner')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama'      => 'required|min_length[3]|max_length[255]',
            'deskripsi' => 'required',
            'status'    => 'required|in_list[Published,Draft]',
        ];

        if ($this->request->getFile('foto')->isValid()) {
            $rules['foto'] = 'is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nama = $this->request->getPost('nama');
        $slug = $this->partnerModel->generateUniqueSlug($nama, $id);

        $updateData = [
            'nama'      => $nama,
            'slug'      => $slug,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ];

        $file = $this->request->getFile('foto');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/partner', $newName);
            $updateData['foto'] = $newName;

            $oldPath = FCPATH . 'uploads/partner/' . $partner['foto'];
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $this->partnerModel->update($id, $updateData);

        return redirect()->to('/admin/partner')->with('success', 'Partner berhasil diperbarui.');
    }

    public function delete($id)
    {
        $partner = $this->partnerModel->find($id);
        if (!$partner) {
            return redirect()->to('/admin/partner')->with('error', 'Data tidak ditemukan.');
        }

        $path = FCPATH . 'uploads/partner/' . $partner['foto'];
        if (is_file($path)) {
            unlink($path);
        }

        $this->partnerModel->delete($id);

        return redirect()->to('/admin/partner')->with('success', 'Partner berhasil dihapus.');
    }
}