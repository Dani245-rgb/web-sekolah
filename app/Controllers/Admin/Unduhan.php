<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UnduhanModel;
use App\Services\FileUploadService;
use Config\Database;
use InvalidArgumentException;
use Throwable;

class Unduhan extends BaseController
{
    private const FOLDER_FILE = 'unduhan';
    private const MAKS_BYTES  = 10 * 1024 * 1024; // 10MB

    /**
     * Ekstensi yang boleh diupload => tipe isi file (MIME) yang sah untuk ekstensi itu.
     * File OOXML (docx/pptx/xlsx) kadang terdeteksi sebagai application/zip, jadi ikut diizinkan.
     * File Office lama (doc/ppt/xls) kadang terdeteksi sebagai application/CDFV2.
     */
    private const TIPE_FILE = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/CDFV2'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'ppt'  => ['application/vnd.ms-powerpoint', 'application/CDFV2'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'xls'  => ['application/vnd.ms-excel', 'application/CDFV2'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'zip'  => ['application/zip'],
    ];

    protected UnduhanModel $unduhanModel;
    protected FileUploadService $uploader;

    public function __construct()
    {
        helper('teks');

        $this->unduhanModel = new UnduhanModel();
        $this->uploader     = service('fileUploadService');
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
        $rules         = $this->baseRules();
        $rules['file'] = 'uploaded[file]';

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = rapikan_teks($this->request->getPost('judul'));

        if ($judul === '') {
            return redirect()->back()->withInput()->with('error', 'Judul tidak boleh kosong/hanya spasi.');
        }

        $db = Database::connect();
        $db->transStart();

        $fileBaru = null;

        try {
            $info     = $this->simpanFile($this->request->getFile('file'));
            $fileBaru = $info['nama_file'];

            $this->unduhanModel->insert([
                'kategori'       => rapikan_teks($this->request->getPost('kategori')),
                'judul'          => $judul,
                'deskripsi'      => $this->request->getPost('deskripsi'),
                'file'           => $info['nama_file'],
                'nama_file_asli' => $info['nama_asli'],
                'ukuran_file'    => $info['ukuran'],
                'ekstensi'       => $info['ekstensi'],
                'status'         => $this->request->getPost('status') ?: 'Draft',
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }
        } catch (Throwable $e) {
            $db->transRollback();

            $this->uploader->hapus($fileBaru, self::FOLDER_FILE);

            log_message('error', 'Gagal simpan Unduhan: ' . $e->getMessage());

            $pesan = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal menyimpan data. Silakan coba lagi.';

            return redirect()->back()->withInput()->with('error', $pesan);
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

        if (!$this->validate($this->baseRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = rapikan_teks($this->request->getPost('judul'));

        if ($judul === '') {
            return redirect()->back()->withInput()->with('error', 'Judul tidak boleh kosong/hanya spasi.');
        }

        $file        = $this->request->getFile('file');
        $adaFileBaru = $file !== null && $file->isValid() && !$file->hasMoved();

        $db = Database::connect();
        $db->transStart();

        $fileBaru = null;
        $fileLama = $item['file'] ?? null;

        try {
            $updateData = [
                'kategori'  => rapikan_teks($this->request->getPost('kategori')),
                'judul'     => $judul,
                'deskripsi' => $this->request->getPost('deskripsi'),
                'status'    => $this->request->getPost('status') ?: 'Draft',
            ];

            if ($adaFileBaru) {
                $info     = $this->simpanFile($file);
                $fileBaru = $info['nama_file'];

                $updateData['file']           = $info['nama_file'];
                $updateData['nama_file_asli'] = $info['nama_asli'];
                $updateData['ukuran_file']    = $info['ukuran'];
                $updateData['ekstensi']       = $info['ekstensi'];
            }

            $this->unduhanModel->update($id, $updateData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            // File lama baru dihapus SETELAH transaksi sukses
            if ($fileBaru) {
                $this->uploader->hapus($fileLama, self::FOLDER_FILE);
            }
        } catch (Throwable $e) {
            $db->transRollback();

            $this->uploader->hapus($fileBaru, self::FOLDER_FILE);

            log_message('error', 'Gagal update Unduhan: ' . $e->getMessage());

            $pesan = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal memperbarui data. Silakan coba lagi.';

            return redirect()->back()->withInput()->with('error', $pesan);
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
            $this->uploader->hapus($item['file'] ?? null, self::FOLDER_FILE);
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

    private function findOrFail($id): ?array
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->unduhanModel->find((int) $id);
    }

    /**
     * Simpan file upload: ekstensi harus ada di daftar TIPE_FILE, dan isi file asli
     * harus cocok dengan ekstensi itu (dicek di FileUploadService).
     *
     * @return array{nama_file: string, nama_asli: string, ukuran: int, ekstensi: string}
     */
    private function simpanFile($file): array
    {
        if ($file === null || !$file->isValid() || $file->hasMoved()) {
            throw new InvalidArgumentException('File tidak valid.');
        }

        $ekstensi = strtolower($file->getClientExtension());

        if (!isset(self::TIPE_FILE[$ekstensi])) {
            throw new InvalidArgumentException('Ekstensi file tidak diizinkan.');
        }

        // Ambil info dulu, sebelum file dipindah
        $namaAsli = $file->getClientName();
        $ukuran   = (int) $file->getSize();

        $namaFile = $this->uploader->simpan($file, self::FOLDER_FILE, self::TIPE_FILE[$ekstensi], self::MAKS_BYTES);

        if ($namaFile === null) {
            throw new InvalidArgumentException('File tidak valid.');
        }

        return [
            'nama_file' => $namaFile,
            'nama_asli' => $namaAsli,
            'ukuran'    => $ukuran,
            'ekstensi'  => $ekstensi,
        ];
    }
}