<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnggotaOrganisasiModel;
use App\Models\OrganisasiModel;
use App\Services\FileUploadService;

class Organisasi extends BaseController
{
    private const FOLDER_ANGGOTA = 'organisasi'; // public/uploads/organisasi

    protected OrganisasiModel $organisasiModel;
    protected AnggotaOrganisasiModel $anggotaModel;
    protected FileUploadService $fileUpload;

    public function __construct()
    {
        helper('teks');

        $this->organisasiModel = new OrganisasiModel();
        $this->anggotaModel    = new AnggotaOrganisasiModel();
        $this->fileUpload      = service('fileUploadService');
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
        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $nama = rapikan_teks((string) $this->request->getPost('nama'));

        if ($this->organisasiModel->namaSudahAda($nama)) {
            return $this->kembaliDenganError(['nama' => 'Nama organisasi sudah dipakai.']);
        }

        $berhasil = $this->organisasiModel->insert([
            'nama'      => $nama,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        if (!$berhasil) {
            return $this->kembaliDenganError($this->organisasiModel->errors());
        }

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
        $item = $this->organisasiModel->find($id);
        if (!$item) {
            return redirect()->to('/admin/organisasi')->with('error', 'Data tidak ditemukan.');
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $nama = rapikan_teks((string) $this->request->getPost('nama'));

        if ($this->organisasiModel->namaSudahAda($nama, (int) $id)) {
            return $this->kembaliDenganError(['nama' => 'Nama organisasi sudah dipakai.']);
        }

        $berhasil = $this->organisasiModel->update($id, [
            'nama'      => $nama,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        if (!$berhasil) {
            return $this->kembaliDenganError($this->organisasiModel->errors());
        }

        return redirect()->to('/admin/organisasi')->with('success', 'Organisasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->organisasiModel->find($id);
        if (!$item) {
            return redirect()->to('/admin/organisasi')->with('error', 'Data tidak ditemukan.');
        }

        // Catat dulu foto anggotanya: record anggota ikut terhapus otomatis
        // (foreign key CASCADE), tapi file fotonya tidak.
        $anggota = $this->anggotaModel->where('id_organisasi', $id)->findAll();

        $this->organisasiModel->delete($id);

        // File dihapus SETELAH record DB terhapus
        foreach ($anggota as $a) {
            $this->fileUpload->hapus($a['foto'], self::FOLDER_ANGGOTA);
        }

        return redirect()->to('/admin/organisasi')->with('success', 'Organisasi berhasil dihapus beserta anggotanya.');
    }

    private function rules(): array
    {
        return [
            'nama'   => 'required|min_length[2]|max_length[100]',
            'status' => 'required|in_list[Published,Draft]',
        ];
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}