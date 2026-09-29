<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PartnerModel;
use App\Services\FileUploadService;
use App\Services\SlugService;
use InvalidArgumentException;
use RuntimeException;

class Partner extends BaseController
{
    private const FOLDER = 'partner'; // public/uploads/partner

    protected PartnerModel $partnerModel;
    protected FileUploadService $fileUpload;
    protected SlugService $slugService;

    public function __construct()
    {
        helper('teks');

        $this->partnerModel = new PartnerModel();
        $this->fileUpload   = service('fileUploadService');
        $this->slugService  = service('slugService');
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
            'foto'      => 'uploaded[foto]',
        ];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $nama = rapikan_teks((string) $this->request->getPost('nama'));

        if ($this->partnerModel->namaSudahAda($nama)) {
            return $this->kembaliDenganError(['nama' => 'Nama partner sudah dipakai.']);
        }

        try {
            $foto = $this->fileUpload->simpan($this->request->getFile('foto'), self::FOLDER);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        if ($foto === null) {
            return $this->kembaliDenganError(['foto' => 'Foto gagal diupload.']);
        }

        $berhasil = $this->partnerModel->insert([
            'nama'      => $nama,
            'slug'      => $this->slugService->buatUnik($nama, 'partner', 'id', null, 'partner'),
            'foto'      => $foto,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        if (!$berhasil) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->fileUpload->hapus($foto, self::FOLDER);
            return $this->kembaliDenganError($this->partnerModel->errors());
        }

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

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $nama = rapikan_teks((string) $this->request->getPost('nama'));

        if ($this->partnerModel->namaSudahAda($nama, (int) $id)) {
            return $this->kembaliDenganError(['nama' => 'Nama partner sudah dipakai.']);
        }

        $dataUpdate = [
            'nama'      => $nama,
            'slug'      => $this->slugService->buatUnik($nama, 'partner', 'id', (int) $id, 'partner'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ];

        // Foto baru (opsional). Kalau tidak diisi, foto lama dipertahankan.
        try {
            $fotoBaru = $this->fileUpload->simpan($this->request->getFile('foto'), self::FOLDER);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        if ($fotoBaru !== null) {
            $dataUpdate['foto'] = $fotoBaru;
        }

        if (!$this->partnerModel->update($id, $dataUpdate)) {
            if ($fotoBaru !== null) {
                $this->fileUpload->hapus($fotoBaru, self::FOLDER);
            }
            return $this->kembaliDenganError($this->partnerModel->errors());
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->fileUpload->hapus($partner['foto'], self::FOLDER);
        }

        return redirect()->to('/admin/partner')->with('success', 'Partner berhasil diperbarui.');
    }

    public function delete($id)
    {
        $partner = $this->partnerModel->find($id);
        if (!$partner) {
            return redirect()->to('/admin/partner')->with('error', 'Data tidak ditemukan.');
        }

        $this->partnerModel->delete($id);

        // File dihapus SETELAH record DB terhapus
        $this->fileUpload->hapus($partner['foto'], self::FOLDER);

        return redirect()->to('/admin/partner')->with('success', 'Partner berhasil dihapus.');
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}