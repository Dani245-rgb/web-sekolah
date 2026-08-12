<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EkstrakurikulerModel;

class Ekstrakurikuler extends BaseController
{
    protected $ekskulModel;

    public function __construct()
    {
        $this->ekskulModel = new EkstrakurikulerModel();
    }

    public function index()
    {
        $data['ekskul'] = $this->ekskulModel->orderBy('nama', 'ASC')->findAll();
        return view('admin/ekstrakurikuler/index', $data);
    }

    public function create()
    {
        $data['ekskul'] = null;
        return view('admin/ekstrakurikuler/form', $data);
    }

    public function store()
    {
        $rules = [
            'nama'   => 'required|min_length[3]|max_length[100]',
            'status' => 'required|in_list[Published,Draft]',
            'foto'   => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file    = $this->request->getFile('foto');
        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/ekstrakurikuler', $newName);

        $this->ekskulModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'foto'      => $newName,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/ekstrakurikuler')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['ekskul'] = $this->ekskulModel->find($id);

        if (!$data['ekskul']) {
            return redirect()->to('/admin/ekstrakurikuler')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/ekstrakurikuler/form', $data);
    }

    public function update($id)
    {
        $ekskul = $this->ekskulModel->find($id);
        if (!$ekskul) {
            return redirect()->to('/admin/ekstrakurikuler')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama'   => 'required|min_length[3]|max_length[100]',
            'status' => 'required|in_list[Published,Draft]',
        ];

        if ($this->request->getFile('foto')->isValid()) {
            $rules['foto'] = 'is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'nama'      => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ];

        $file = $this->request->getFile('foto');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/ekstrakurikuler', $newName);
            $updateData['foto'] = $newName;

            $oldPath = FCPATH . 'uploads/ekstrakurikuler/' . $ekskul['foto'];
            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $this->ekskulModel->update($id, $updateData);

        return redirect()->to('/admin/ekstrakurikuler')->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function delete($id)
    {
        $ekskul = $this->ekskulModel->find($id);
        if (!$ekskul) {
            return redirect()->to('/admin/ekstrakurikuler')->with('error', 'Data tidak ditemukan.');
        }

        $path = FCPATH . 'uploads/ekstrakurikuler/' . $ekskul['foto'];
        if (is_file($path)) {
            unlink($path);
        }

        $this->ekskulModel->delete($id);

        return redirect()->to('/admin/ekstrakurikuler')->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }
}