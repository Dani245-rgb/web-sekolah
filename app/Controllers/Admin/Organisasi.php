<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrganisasiModel;

class Organisasi extends BaseController
{
    protected $organisasiModel;

    public function __construct()
    {
        helper('text');
        $this->organisasiModel = new OrganisasiModel();
    }

    public function index()
    {
        $data['organisasi'] = $this->organisasiModel->orderBy('nama', 'ASC')->findAll();
        return view('admin/organisasi/index', $data);
    }

    public function create()
    {
        $data['item'] = null;
        return view('admin/organisasi/form', $data);
    }

    public function store()
    {
        $rules = [
            'nama'   => 'required|min_length[2]|max_length[100]',
            'status' => 'required|in_list[Published,Draft]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->organisasiModel->insert([
            'nama'      => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/organisasi')->with('success', 'Organisasi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['item'] = $this->organisasiModel->find($id);

        if (!$data['item']) {
            return redirect()->to('/admin/organisasi')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/organisasi/form', $data);
    }

    public function update($id)
    {
        $rules = [
            'nama'   => 'required|min_length[2]|max_length[100]',
            'status' => 'required|in_list[Published,Draft]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->organisasiModel->update($id, [
            'nama'      => $this->request->getPost('nama'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/organisasi')->with('success', 'Organisasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->organisasiModel->find($id);
        if (!$item) {
            return redirect()->to('/admin/organisasi')->with('error', 'Data tidak ditemukan.');
        }

        // anggota ikut terhapus otomatis (foreign key CASCADE)
        $this->organisasiModel->delete($id);

        return redirect()->to('/admin/organisasi')->with('success', 'Organisasi berhasil dihapus beserta anggotanya.');
    }
}