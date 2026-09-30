<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EkstrakurikulerModel;
use App\Services\FileUploadService;
use InvalidArgumentException;
use RuntimeException;

class Ekstrakurikuler extends BaseController
{
    private const FOLDER = 'ekstrakurikuler'; // public/uploads/ekstrakurikuler

    protected EkstrakurikulerModel $ekskulModel;
    protected FileUploadService $fileUpload;

    public function __construct()
    {
        helper('teks');

        $this->ekskulModel = new EkstrakurikulerModel();
        $this->fileUpload  = service('fileUploadService');
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
        $rules = $this->rules() + ['foto' => 'uploaded[foto]'];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $nama = rapikan_teks((string) $this->request->getPost('nama'));

        if ($this->ekskulModel->namaSudahAda($nama)) {
            return $this->kembaliDenganError(['nama' => 'Nama ekstrakurikuler sudah dipakai.']);
        }

        try {
            $foto = $this->fileUpload->simpan($this->request->getFile('foto'), self::FOLDER);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        if ($foto === null) {
            return $this->kembaliDenganError(['foto' => 'Foto gagal diupload.']);
        }

        $berhasil = $this->ekskulModel->insert([
            'nama'      => $nama,
            'foto'      => $foto,
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        if (!$berhasil) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->fileUpload->hapus($foto, self::FOLDER);
            return $this->kembaliDenganError($this->ekskulModel->errors());
        }

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

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $nama = rapikan_teks((string) $this->request->getPost('nama'));

        if ($this->ekskulModel->namaSudahAda($nama, (int) $id)) {
            return $this->kembaliDenganError(['nama' => 'Nama ekstrakurikuler sudah dipakai.']);
        }

        $dataUpdate = [
            'nama'      => $nama,
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

        if (!$this->ekskulModel->update($id, $dataUpdate)) {
            if ($fotoBaru !== null) {
                $this->fileUpload->hapus($fotoBaru, self::FOLDER);
            }
            return $this->kembaliDenganError($this->ekskulModel->errors());
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->fileUpload->hapus($ekskul['foto'], self::FOLDER);
        }

        return redirect()->to('/admin/ekstrakurikuler')->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function delete($id)
    {
        $ekskul = $this->ekskulModel->find($id);
        if (!$ekskul) {
            return redirect()->to('/admin/ekstrakurikuler')->with('error', 'Data tidak ditemukan.');
        }

        $this->ekskulModel->delete($id);

        // File dihapus SETELAH record DB terhapus
        $this->fileUpload->hapus($ekskul['foto'], self::FOLDER);

        return redirect()->to('/admin/ekstrakurikuler')->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'nama'   => 'required|min_length[3]|max_length[100]',
            'status' => 'required|in_list[Published,Draft]',
        ];
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}