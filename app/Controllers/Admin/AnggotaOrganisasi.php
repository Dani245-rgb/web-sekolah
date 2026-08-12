<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnggotaOrganisasiModel;
use App\Models\OrganisasiModel;

class AnggotaOrganisasi extends BaseController
{
    protected $anggotaModel;
    protected $organisasiModel;

    public function __construct()
    {
        $this->anggotaModel    = new AnggotaOrganisasiModel();
        $this->organisasiModel = new OrganisasiModel();
    }

    public function index($idOrganisasi)
    {
        $organisasi = $this->organisasiModel->find($idOrganisasi);
        if (!$organisasi) {
            return redirect()->to('/admin/organisasi')->with('error', 'Organisasi tidak ditemukan.');
        }

        $data['organisasi'] = $organisasi;
        $data['anggota']    = $this->anggotaModel->where('id_organisasi', $idOrganisasi)
            ->orderBy('urutan', 'ASC')
            ->findAll();

        return view('admin/anggota_organisasi/index', $data);
    }

    public function create($idOrganisasi)
    {
        $organisasi = $this->organisasiModel->find($idOrganisasi);
        if (!$organisasi) {
            return redirect()->to('/admin/organisasi')->with('error', 'Organisasi tidak ditemukan.');
        }

        $data['organisasi'] = $organisasi;
        $data['item']       = null;

        return view('admin/anggota_organisasi/form', $data);
    }

    public function store($idOrganisasi)
    {
        $rules = [
            'nama'    => 'required|min_length[2]|max_length[255]',
            'jabatan' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fotoName = null;
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fotoName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/organisasi', $fotoName);
        }

        $this->anggotaModel->insert([
            'id_organisasi' => $idOrganisasi,
            'nama'          => $this->request->getPost('nama'),
            'jabatan'       => $this->request->getPost('jabatan'),
            'foto'          => $fotoName,
            'urutan'        => (int) $this->request->getPost('urutan'),
        ]);

        return redirect()->to('/admin/organisasi/' . $idOrganisasi . '/anggota')->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit($idOrganisasi, $id)
    {
        $data['organisasi'] = $this->organisasiModel->find($idOrganisasi);
        $data['item']       = $this->anggotaModel->find($id);

        if (!$data['item']) {
            return redirect()->to('/admin/organisasi/' . $idOrganisasi . '/anggota')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/anggota_organisasi/form', $data);
    }

    public function update($idOrganisasi, $id)
    {
        $item = $this->anggotaModel->find($id);
        if (!$item) {
            return redirect()->to('/admin/organisasi/' . $idOrganisasi . '/anggota')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'nama'    => 'required|min_length[2]|max_length[255]',
            'jabatan' => 'required|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'nama'    => $this->request->getPost('nama'),
            'jabatan' => $this->request->getPost('jabatan'),
            'urutan'  => (int) $this->request->getPost('urutan'),
        ];

        $file = $this->request->getFile('foto');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/organisasi', $newName);
            $updateData['foto'] = $newName;

            if ($item['foto'] && is_file(FCPATH . 'uploads/organisasi/' . $item['foto'])) {
                unlink(FCPATH . 'uploads/organisasi/' . $item['foto']);
            }
        }

        $this->anggotaModel->update($id, $updateData);

        return redirect()->to('/admin/organisasi/' . $idOrganisasi . '/anggota')->with('success', 'Anggota berhasil diperbarui.');
    }

    public function delete($idOrganisasi, $id)
    {
        $item = $this->anggotaModel->find($id);
        if ($item) {
            if ($item['foto'] && is_file(FCPATH . 'uploads/organisasi/' . $item['foto'])) {
                unlink(FCPATH . 'uploads/organisasi/' . $item['foto']);
            }
            $this->anggotaModel->delete($id);
        }

        return redirect()->to('/admin/organisasi/' . $idOrganisasi . '/anggota')->with('success', 'Anggota berhasil dihapus.');
    }
}