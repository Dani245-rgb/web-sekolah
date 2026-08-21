<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UnduhanModel;
use Throwable;

class Unduhan extends BaseController
{
    protected $unduhanModel;

    protected $ekstensiDiizinkan = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip'];

    public function __construct()
    {
        $this->unduhanModel = new UnduhanModel();
    }

    public function index()
    {
        $kategoriFilter = $this->request->getGet('kategori');

        $query = $this->unduhanModel->orderBy('created_at', 'DESC');

        if ($kategoriFilter) {
            $query->where('kategori', $kategoriFilter);
        }

        $data['unduhanList']    = $query->findAll();
        $data['kategoriList']   = $this->unduhanModel->getKategoriList();
        $data['kategoriFilter'] = $kategoriFilter;

        return view('admin/unduhan/index', $data);
    }

    public function create()
    {
        $data['kategoriList'] = $this->unduhanModel->getKategoriList();
        return view('admin/unduhan/create', $data);
    }

    public function store()
    {
        $rules = $this->baseRules();
        $rules['file'] = 'uploaded[file]|' . $this->fileRule();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = trim((string) $this->request->getPost('judul'));

        if ($judul === '') {
            return redirect()->back()->withInput()->with('error', 'Judul tidak boleh kosong/hanya spasi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $fileBaru = null;

        try {
            $file = $this->request->getFile('file');

            $fileInfo = $this->handleUploadFile($file);
            $fileBaru = $fileInfo['nama_file'];

            $insertData = [
                'kategori'       => trim((string) $this->request->getPost('kategori')),
                'judul'          => $judul,
                'deskripsi'      => $this->request->getPost('deskripsi'),
                'file'           => $fileInfo['nama_file'],
                'nama_file_asli' => $fileInfo['nama_asli'],
                'ukuran_file'    => $fileInfo['ukuran'],
                'ekstensi'       => $fileInfo['ekstensi'],
                'status'         => $this->request->getPost('status') ?: 'Draft',
            ];

            $this->unduhanModel->insert($insertData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }
        } catch (Throwable $e) {
            $db->transRollback();

            if ($fileBaru && is_file(FCPATH . 'uploads/unduhan/' . $fileBaru)) {
                unlink(FCPATH . 'uploads/unduhan/' . $fileBaru);
            }

            log_message('error', 'Gagal simpan Unduhan: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/unduhan')->with('success', 'File berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/unduhan')->with('error', 'Data tidak ditemukan.');
        }

        $data['item']         = $item;
        $data['kategoriList'] = $this->unduhanModel->getKategoriList();

        return view('admin/unduhan/edit', $data);
    }

    public function update($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/unduhan')->with('error', 'Data tidak ditemukan.');
        }

        $rules = $this->baseRules();

        $file = $this->request->getFile('file');
        if ($file !== null && $file->isValid() && !$file->hasMoved()) {
            $rules['file'] = $this->fileRule();
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = trim((string) $this->request->getPost('judul'));

        if ($judul === '') {
            return redirect()->back()->withInput()->with('error', 'Judul tidak boleh kosong/hanya spasi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $fileBaru = null;
        $fileLama = $item['file'] ?? null;

        try {
            $updateData = [
                'kategori'  => trim((string) $this->request->getPost('kategori')),
                'judul'     => $judul,
                'deskripsi' => $this->request->getPost('deskripsi'),
                'status'    => $this->request->getPost('status') ?: 'Draft',
            ];

            if ($file !== null && $file->isValid() && !$file->hasMoved()) {
                $fileInfo = $this->handleUploadFile($file);
                $fileBaru = $fileInfo['nama_file'];

                $updateData['file']           = $fileInfo['nama_file'];
                $updateData['nama_file_asli'] = $fileInfo['nama_asli'];
                $updateData['ukuran_file']    = $fileInfo['ukuran'];
                $updateData['ekstensi']       = $fileInfo['ekstensi'];
            }

            $this->unduhanModel->update($id, $updateData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            if ($fileBaru && $fileLama && is_file(FCPATH . 'uploads/unduhan/' . $fileLama)) {
                unlink(FCPATH . 'uploads/unduhan/' . $fileLama);
            }
        } catch (Throwable $e) {
            $db->transRollback();

            if ($fileBaru && is_file(FCPATH . 'uploads/unduhan/' . $fileBaru)) {
                unlink(FCPATH . 'uploads/unduhan/' . $fileBaru);
            }

            log_message('error', 'Gagal update Unduhan: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/unduhan')->with('success', 'File berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/unduhan')->with('error', 'Data tidak ditemukan.');
        }

        try {
            $this->unduhanModel->delete($id);

            if (!empty($item['file']) && is_file(FCPATH . 'uploads/unduhan/' . $item['file'])) {
                unlink(FCPATH . 'uploads/unduhan/' . $item['file']);
            }
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Unduhan: ' . $e->getMessage());
            return redirect()->to('/admin/unduhan')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/unduhan')->with('success', 'File berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function baseRules(): array
    {
        return [
            'kategori'  => 'required|max_length[100]',
            'judul'     => 'required|max_length[255]',
            'deskripsi' => 'permit_empty|max_length[2000]',
            'status'    => 'permit_empty|in_list[Draft,Published]',
        ];
    }

    private function fileRule(): string
    {
        $ekstensiStr = implode(',', $this->ekstensiDiizinkan);
        return "ext_in[file,{$ekstensiStr}]|max_size[file,10240]";
    }

    private function findOrFail($id): ?array
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->unduhanModel->find((int) $id);
    }

    private function handleUploadFile($file): array
    {
        if ($file === null || !$file->isValid() || $file->hasMoved()) {
            throw new \RuntimeException('File tidak valid.');
        }

        $targetDir = FCPATH . 'uploads/unduhan';

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new \RuntimeException('Folder upload tidak dapat dibuat.');
            }
        }

        if (!is_writable($targetDir)) {
            throw new \RuntimeException('Folder upload tidak dapat ditulis (cek permission).');
        }

        $namaAsli = $file->getClientName();
        $ukuran   = $file->getSize();
        $ekstensi = strtolower($file->getClientExtension());
        $newName  = $file->getRandomName();

        if (!$file->move($targetDir, $newName)) {
            throw new \RuntimeException('Gagal memindahkan file upload.');
        }

        return [
            'nama_file' => $newName,
            'nama_asli' => $namaAsli,
            'ukuran'    => $ukuran,
            'ekstensi'  => $ekstensi,
        ];
    }
}