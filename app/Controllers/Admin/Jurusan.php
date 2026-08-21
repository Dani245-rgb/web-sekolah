<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JurusanModel;
use App\Models\KelasModel;
use Throwable;

class Jurusan extends BaseController
{
    protected $jurusanModel;

    protected $rulesLabel = [
        'nama_jurusan'  => 'Nama Jurusan',
        'deskripsi'     => 'Deskripsi',
        'kompetensi'    => 'Kompetensi',
        // 'prospek_kerja' => 'Prospek Kerja',
        'foto'          => 'Foto',
    ];

    public function __construct()
    {
        $this->jurusanModel = new JurusanModel();
    }

    public function index()
    {
        $data['jurusanList'] = $this->jurusanModel->orderBy('nama_jurusan', 'ASC')->findAll();
        return view('admin/jurusan/index', $data);
    }

    public function create()
    {
        return view('admin/jurusan/create');
    }

    public function store()
    {
        $rules = [
            'nama_jurusan'  => 'required|max_length[100]|is_unique[jurusan.nama_jurusan]',
            'singkatan'     => 'permit_empty|max_length[20]',
            'deskripsi'     => 'permit_empty',
            'kompetensi'    => 'permit_empty',
            'prospek_kerja' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaJurusan = trim((string) $this->request->getPost('nama_jurusan'));

        if ($namaJurusan === '') {
            return redirect()->back()->withInput()->with('error', 'Nama jurusan tidak boleh kosong/hanya spasi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $fotoBaru = null;

        try {
            $insertData = [
                'nama_jurusan'  => $namaJurusan,
                'slug'          => $this->generateUniqueSlug($namaJurusan),
                'deskripsi'     => $this->request->getPost('deskripsi'),
                'kompetensi'    => $this->request->getPost('kompetensi'),
                'prospek_kerja' => $this->request->getPost('prospek_kerja'),
            ];

            $fotoBaru = $this->handleUploadFoto();
            if ($fotoBaru) {
                $insertData['foto'] = $fotoBaru;
            }

            $this->jurusanModel->insert($insertData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }
        } catch (Throwable $e) {
            $db->transRollback();

            // Kalau sempat upload foto tapi transaksi gagal, hapus filenya biar tidak jadi sampah
            if ($fotoBaru && is_file(FCPATH . 'uploads/jurusan/' . $fotoBaru)) {
                unlink(FCPATH . 'uploads/jurusan/' . $fotoBaru);
            }

            log_message('error', 'Gagal simpan Jurusan: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/jurusan')->with('error', 'Jurusan tidak ditemukan.');
        }

        $data['item'] = $item;
        return view('admin/jurusan/edit', $data);
    }

    public function update($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/jurusan')->with('error', 'Jurusan tidak ditemukan.');
        }

        $rules = [
            'nama_jurusan'  => "required|max_length[100]|is_unique[jurusan.nama_jurusan,id_jurusan,{$id}]",
            'singkatan'     => 'permit_empty|max_length[20]',
            'deskripsi'     => 'permit_empty',
            'kompetensi'    => 'permit_empty',
            'prospek_kerja' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaJurusan = trim((string) $this->request->getPost('nama_jurusan'));

        if ($namaJurusan === '') {
            return redirect()->back()->withInput()->with('error', 'Nama jurusan tidak boleh kosong/hanya spasi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $fotoBaru  = null;
        $fotoLama  = $item['foto'] ?? null;

        try {
            $updateData = [
                'nama_jurusan'  => $namaJurusan,
                'deskripsi'     => $this->request->getPost('deskripsi'),
                'kompetensi'    => $this->request->getPost('kompetensi'),
                'prospek_kerja' => $this->request->getPost('prospek_kerja'),
            ];

            if ($namaJurusan !== $item['nama_jurusan']) {
                $updateData['slug'] = $this->generateUniqueSlug($namaJurusan, (int) $id);
            }

            $fotoBaru = $this->handleUploadFoto();
            if ($fotoBaru) {
                $updateData['foto'] = $fotoBaru;
            }

            $this->jurusanModel->update($id, $updateData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            // Baru hapus foto lama SETELAH transaksi sukses, biar aman kalau gagal di tengah
            if ($fotoBaru && $fotoLama && is_file(FCPATH . 'uploads/jurusan/' . $fotoLama)) {
                unlink(FCPATH . 'uploads/jurusan/' . $fotoLama);
            }
        } catch (Throwable $e) {
            $db->transRollback();

            if ($fotoBaru && is_file(FCPATH . 'uploads/jurusan/' . $fotoBaru)) {
                unlink(FCPATH . 'uploads/jurusan/' . $fotoBaru);
            }

            log_message('error', 'Gagal update Jurusan: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/jurusan')->with('error', 'Jurusan tidak ditemukan.');
        }

        $kelasModel  = new KelasModel();
        $jumlahKelas = $kelasModel->where('id_jurusan', $id)->countAllResults();

        if ($jumlahKelas > 0) {
            return redirect()->to('/admin/jurusan')
                ->with('error', "Tidak bisa dihapus — masih dipakai oleh {$jumlahKelas} kelas. Pindahkan kelas ke jurusan lain dulu.");
        }

        try {
            $this->jurusanModel->delete($id);

            if (!empty($item['foto']) && is_file(FCPATH . 'uploads/jurusan/' . $item['foto'])) {
                unlink(FCPATH . 'uploads/jurusan/' . $item['foto']);
            }
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Jurusan: ' . $e->getMessage());
            return redirect()->to('/admin/jurusan')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function baseRules(): array
    {
        $rules = [
            'nama_jurusan'  => 'required|max_length[100]',
            'deskripsi'     => 'permit_empty|max_length[5000]',
            'kompetensi'    => 'permit_empty|max_length[5000]',
            'prospek_kerja' => 'permit_empty|max_length[5000]',
        ];

        $file = $this->request->getFile('foto');
        if ($file !== null && $file->isValid() && !$file->hasMoved()) {
            $rules['foto'] = 'is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]';
        }

        return $rules;
    }

    /**
     * Cari jurusan berdasarkan ID. Null-safe: kalau ID bukan angka valid atau tidak ada, return null.
     */
    private function findOrFail($id): ?array
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->jurusanModel->find((int) $id);
    }

    /**
     * Upload foto kalau ada file valid. Return nama file baru, atau null kalau tidak ada upload.
     * Melempar exception kalau file gagal dipindah (misal folder tidak writable),
     * biar caller bisa rollback transaksi alih-alih data setengah tersimpan.
     */
    private function handleUploadFoto(): ?string
    {
        $file = $this->request->getFile('foto');

        if ($file === null || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $targetDir = FCPATH . 'uploads/jurusan';

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new \RuntimeException('Folder upload tidak dapat dibuat.');
            }
        }

        if (!is_writable($targetDir)) {
            throw new \RuntimeException('Folder upload tidak dapat ditulis (cek permission).');
        }

        $newName = $file->getRandomName();

        if (!$file->move($targetDir, $newName)) {
            throw new \RuntimeException('Gagal memindahkan file upload.');
        }

        return $newName;
    }

    /**
     * Generate slug unik. $excludeId dipakai saat update biar tidak bentrok sama dirinya sendiri.
     */
    private function generateUniqueSlug(string $nama, ?int $excludeId = null): string
    {
        $slugAsli = url_title($nama, '-', true);

        if ($slugAsli === '') {
            // Fallback kalau nama_jurusan isinya karakter aneh semua & url_title menghasilkan string kosong
            $slugAsli = 'jurusan-' . time();
        }

        $slug    = $slugAsli;
        $counter = 1;

        while (true) {
            $builder = $this->jurusanModel->where('slug', $slug);
            if ($excludeId !== null) {
                $builder->where('id_jurusan !=', $excludeId);
            }

            if (!$builder->first()) {
                break;
            }

            $slug = $slugAsli . '-' . $counter;
            $counter++;

            // Guard rail: jangan sampai infinite loop kalau ada bug lain
            if ($counter > 1000) {
                $slug = $slugAsli . '-' . uniqid();
                break;
            }
        }

        return $slug;
    }
}
