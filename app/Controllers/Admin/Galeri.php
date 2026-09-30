<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GaleriModel;
use App\Services\FileUploadService;
use InvalidArgumentException;
use RuntimeException;

class Galeri extends BaseController
{
    private const FOLDER = 'galeri'; // public/uploads/galeri

    protected GaleriModel $galeriModel;
    protected FileUploadService $fileUpload;

    public function __construct()
    {
        helper('teks');

        $this->galeriModel = new GaleriModel();
        $this->fileUpload  = service('fileUploadService');
    }

    public function index()
    {
        $data['galeri'] = $this->galeriModel->orderBy('created_at', 'DESC')->findAll();
        return view('admin/galeri/index', $data);
    }

    public function create()
    {
        $data['galeri'] = null;
        return view('admin/galeri/form', $data);
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

        $berhasil = $this->galeriModel->insert([
            'judul'     => rapikan_teks((string) $this->request->getPost('judul')),
            'foto'      => $foto,
            'kategori'  => $this->request->getPost('kategori'),
            'deskripsi' => $this->request->getPost('deskripsi'),
            'status'    => $this->request->getPost('status'),
        ]);

        if (!$berhasil) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->fileUpload->hapus($foto, self::FOLDER);
            return $this->kembaliDenganError($this->galeriModel->errors());
        }

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['galeri'] = $this->galeriModel->find($id);

        if (!$data['galeri']) {
            return redirect()->to('/admin/galeri')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/galeri/form', $data);
    }

    public function update($id)
    {
        $galeri = $this->galeriModel->find($id);
        if (!$galeri) {
            return redirect()->to('/admin/galeri')->with('error', 'Data tidak ditemukan.');
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $dataUpdate = [
            'judul'     => rapikan_teks((string) $this->request->getPost('judul')),
            'kategori'  => $this->request->getPost('kategori'),
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

        if (!$this->galeriModel->update($id, $dataUpdate)) {
            if ($fotoBaru !== null) {
                $this->fileUpload->hapus($fotoBaru, self::FOLDER);
            }
            return $this->kembaliDenganError($this->galeriModel->errors());
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->fileUpload->hapus($galeri['foto'], self::FOLDER);
        }

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil diperbarui.');
    }

    public function delete($id)
    {
        $galeri = $this->galeriModel->find($id);
        if (!$galeri) {
            return redirect()->to('/admin/galeri')->with('error', 'Data tidak ditemukan.');
        }

        $this->galeriModel->delete($id);

        // File dihapus SETELAH record DB terhapus
        $this->fileUpload->hapus($galeri['foto'], self::FOLDER);

        return redirect()->to('/admin/galeri')->with('success', 'Foto berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'judul'    => 'required|min_length[3]|max_length[255]',
            'kategori' => 'required|in_list[Kegiatan,Fasilitas,Prestasi,Lainnya]',
            'status'   => 'required|in_list[Published,Draft]',
        ];
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}