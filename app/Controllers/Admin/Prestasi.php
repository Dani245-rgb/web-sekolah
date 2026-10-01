<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PrestasiModel;
use App\Services\FileUploadService;
use InvalidArgumentException;
use RuntimeException;

class Prestasi extends BaseController
{
    private const FOLDER = 'prestasi'; // public/uploads/prestasi

    protected PrestasiModel $prestasiModel;
    protected FileUploadService $fileUpload;

    public function __construct()
    {
        helper('teks');

        $this->prestasiModel = new PrestasiModel();
        $this->fileUpload    = service('fileUploadService');
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
        $rules = $this->rules() + ['foto' => 'uploaded[foto]'];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $foto = $this->fileUpload->simpan($this->request->getFile('foto'), self::FOLDER);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        if ($foto === null) {
            return $this->kembaliDenganError(['foto' => 'Foto gagal diupload.']);
        }

        $berhasil = $this->prestasiModel->insert([
            'judul'   => rapikan_teks((string) $this->request->getPost('judul')),
            'tim'     => rapikan_teks((string) $this->request->getPost('tim')),
            'tingkat' => $this->request->getPost('tingkat'),
            'foto'    => $foto,
            'tanggal' => $this->request->getPost('tanggal'),
            'status'  => $this->request->getPost('status'),
        ]);

        if (!$berhasil) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->fileUpload->hapus($foto, self::FOLDER);
            return $this->kembaliDenganError($this->prestasiModel->errors());
        }

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

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $dataUpdate = [
            'judul'   => rapikan_teks((string) $this->request->getPost('judul')),
            'tim'     => rapikan_teks((string) $this->request->getPost('tim')),
            'tingkat' => $this->request->getPost('tingkat'),
            'tanggal' => $this->request->getPost('tanggal'),
            'status'  => $this->request->getPost('status'),
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

        if (!$this->prestasiModel->update($id, $dataUpdate)) {
            if ($fotoBaru !== null) {
                $this->fileUpload->hapus($fotoBaru, self::FOLDER);
            }
            return $this->kembaliDenganError($this->prestasiModel->errors());
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->fileUpload->hapus($prestasi['foto'], self::FOLDER);
        }

        return redirect()->to('/admin/prestasi')->with('success', 'Prestasi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $prestasi = $this->prestasiModel->find($id);
        if (!$prestasi) {
            return redirect()->to('/admin/prestasi')->with('error', 'Data tidak ditemukan.');
        }

        $this->prestasiModel->delete($id);

        // File dihapus SETELAH record DB terhapus
        $this->fileUpload->hapus($prestasi['foto'], self::FOLDER);

        return redirect()->to('/admin/prestasi')->with('success', 'Prestasi berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'judul'   => 'required|min_length[3]|max_length[255]',
            'tingkat' => 'required|in_list[Kabupaten,Provinsi,Nasional,Internasional]',
            'tanggal' => 'required|valid_date',
            'status'  => 'required|in_list[Published,Draft]',
        ];
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}   