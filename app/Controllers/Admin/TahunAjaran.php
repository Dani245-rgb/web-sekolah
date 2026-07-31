<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TahunAjaranModel;

class TahunAjaran extends BaseController
{
    protected TahunAjaranModel $tahunAjaranModel;

    public function __construct()
    {
        $this->tahunAjaranModel = new TahunAjaranModel();
    }

    public function index()
    {
        $data['tahunAjaran'] = $this->tahunAjaranModel->orderBy('tahun_ajaran', 'DESC')->findAll();
        return view('admin/tahun_ajaran/index', $data);
    }

    public function create()
    {
        return view('admin/tahun_ajaran/create');
    }

    public function store()
    {
        $rules = [
            'tahun_ajaran'    => 'required|max_length[20]|is_unique[tahun_ajaran.tahun_ajaran]',
            'semester'        => 'permit_empty|in_list[Ganjil,Genap]',
            'tanggal_mulai'   => 'permit_empty|valid_date',
            'tanggal_selesai' => 'permit_empty|valid_date',
            'status'          => 'required|in_list[Aktif,Tidak Aktif,Belum Aktif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = $this->tahunAjaranModel->insert([
            'tahun_ajaran'    => $this->request->getPost('tahun_ajaran'),
            'semester'        => $this->request->getPost('semester'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'status'          => $this->request->getPost('status'),
        ]);

        // Kalau baru dibuat langsung diset Aktif, matikan yang lain
        if ($this->request->getPost('status') === 'Aktif') {
            $this->tahunAjaranModel->setAsActive($id);
        }

        return redirect()->to('/admin/tahun-ajaran')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['tahunAjaran'] = $this->tahunAjaranModel->find($id);

        if (!$data['tahunAjaran']) {
            return redirect()->to('/admin/tahun-ajaran')->with('errors', ['404' => 'Data tidak ditemukan.']);
        }

        return view('admin/tahun_ajaran/edit', $data);
    }

    public function update($id)
    {
        $row = $this->tahunAjaranModel->find($id);
        if (!$row) {
            return redirect()->to('/admin/tahun-ajaran')->with('errors', ['404' => 'Data tidak ditemukan.']);
        }

        $rules = [
            'tahun_ajaran'    => "required|max_length[20]|is_unique[tahun_ajaran.tahun_ajaran,id_tahun_ajaran,{$id}]",
            'semester'        => 'permit_empty|in_list[Ganjil,Genap]',
            'tanggal_mulai'   => 'permit_empty|valid_date',
            'tanggal_selesai' => 'permit_empty|valid_date',
            'status'          => 'required|in_list[Aktif,Tidak Aktif,Belum Aktif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $status = $this->request->getPost('status');

        $this->tahunAjaranModel->update($id, [
            'tahun_ajaran'    => $this->request->getPost('tahun_ajaran'),
            'semester'        => $this->request->getPost('semester'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'status'          => $status,
        ]);

        if ($status === 'Aktif') {
            $this->tahunAjaranModel->setAsActive($id);
        }

        return redirect()->to('/admin/tahun-ajaran')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function delete($id)
    {
        $row = $this->tahunAjaranModel->find($id);
        if (!$row) {
            return redirect()->to('/admin/tahun-ajaran')->with('errors', ['404' => 'Data tidak ditemukan.']);
        }

        // Cegah hapus kalau sudah dipakai Kelas (FK RESTRICT bakal error kalau dipaksa)
        $db = \Config\Database::connect();
        $dipakai = $db->table('kelas')->where('id_tahun_ajaran', $id)->countAllResults();

        if ($dipakai > 0) {
            return redirect()->to('/admin/tahun-ajaran')
                ->with('errors', ['used' => 'Tidak bisa dihapus, tahun ajaran ini sudah dipakai di data Kelas.']);
        }

        $this->tahunAjaranModel->delete($id);

        return redirect()->to('/admin/tahun-ajaran')->with('success', 'Tahun ajaran berhasil dihapus.');
    }
}