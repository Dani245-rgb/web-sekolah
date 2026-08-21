<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BkArtikelModel;
use Throwable;

class BkArtikel extends BaseController
{
    protected $artikelModel;

    protected $kategoriValid = ['kesehatan_mental', 'karier', 'tes_minat'];

    public function __construct()
    {
        $this->artikelModel = new BkArtikelModel();
    }

    public function index()
    {
        $kategoriFilter = $this->request->getGet('kategori');

        $query = $this->artikelModel->orderBy('created_at', 'DESC');

        if ($kategoriFilter && in_array($kategoriFilter, $this->kategoriValid, true)) {
            $query->where('kategori', $kategoriFilter);
        }

        $data['artikelList']    = $query->findAll();
        $data['kategoriFilter'] = $kategoriFilter;

        return view('admin/bk_artikel/index', $data);
    }

    public function create()
    {
        return view('admin/bk_artikel/create');
    }

    public function store()
    {
        $rules = $this->baseRules();
        $rules['judul'] .= '|is_unique[bk_artikel.judul]';

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = trim((string) $this->request->getPost('judul'));

        if ($judul === '') {
            return redirect()->back()->withInput()->with('error', 'Judul tidak boleh kosong/hanya spasi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $fotoBaru = null;

        try {
            $insertData = [
                'kategori'        => $this->request->getPost('kategori'),
                'judul'           => $judul,
                'slug'            => $this->generateUniqueSlug($judul),
                'konten'          => $this->request->getPost('konten'),
                'link_eksternal'  => $this->request->getPost('link_eksternal') ?: null,
                'deadline'        => $this->request->getPost('deadline') ?: null,
                'status'          => $this->request->getPost('status') ?: 'Draft',
                'id_guru_penulis' => $this->resolveIdGuruPenulis(),
            ];

            $fotoBaru = $this->handleUploadFoto();
            if ($fotoBaru) {
                $insertData['foto'] = $fotoBaru;
            }

            $this->artikelModel->insert($insertData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }
        } catch (Throwable $e) {
            $db->transRollback();

            if ($fotoBaru && is_file(FCPATH . 'uploads/bk_artikel/' . $fotoBaru)) {
                unlink(FCPATH . 'uploads/bk_artikel/' . $fotoBaru);
            }

            log_message('error', 'Gagal simpan Artikel BK: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/bk-artikel')->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/bk-artikel')->with('error', 'Artikel tidak ditemukan.');
        }

        $data['item'] = $item;
        return view('admin/bk_artikel/edit', $data);
    }

    public function update($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/bk-artikel')->with('error', 'Artikel tidak ditemukan.');
        }

        $rules = $this->baseRules();
        $rules['judul'] .= "|is_unique[bk_artikel.judul,id_artikel,{$id}]";

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = trim((string) $this->request->getPost('judul'));

        if ($judul === '') {
            return redirect()->back()->withInput()->with('error', 'Judul tidak boleh kosong/hanya spasi.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $fotoBaru = null;
        $fotoLama = $item['foto'] ?? null;

        try {
            $updateData = [
                'kategori'       => $this->request->getPost('kategori'),
                'judul'          => $judul,
                'konten'         => $this->request->getPost('konten'),
                'link_eksternal' => $this->request->getPost('link_eksternal') ?: null,
                'deadline'       => $this->request->getPost('deadline') ?: null,
                'status'         => $this->request->getPost('status') ?: 'Draft',
            ];

            if ($judul !== $item['judul']) {
                $updateData['slug'] = $this->generateUniqueSlug($judul, (int) $id);
            }

            $fotoBaru = $this->handleUploadFoto();
            if ($fotoBaru) {
                $updateData['foto'] = $fotoBaru;
            }

            $this->artikelModel->update($id, $updateData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            if ($fotoBaru && $fotoLama && is_file(FCPATH . 'uploads/bk_artikel/' . $fotoLama)) {
                unlink(FCPATH . 'uploads/bk_artikel/' . $fotoLama);
            }
        } catch (Throwable $e) {
            $db->transRollback();

            if ($fotoBaru && is_file(FCPATH . 'uploads/bk_artikel/' . $fotoBaru)) {
                unlink(FCPATH . 'uploads/bk_artikel/' . $fotoBaru);
            }

            log_message('error', 'Gagal update Artikel BK: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/bk-artikel')->with('success', 'Artikel berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/bk-artikel')->with('error', 'Artikel tidak ditemukan.');
        }

        try {
            $this->artikelModel->delete($id);

            if (!empty($item['foto']) && is_file(FCPATH . 'uploads/bk_artikel/' . $item['foto'])) {
                unlink(FCPATH . 'uploads/bk_artikel/' . $item['foto']);
            }
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Artikel BK: ' . $e->getMessage());
            return redirect()->to('/admin/bk-artikel')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/bk-artikel')->with('success', 'Artikel berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function baseRules(): array
    {
        $rules = [
            'kategori' => 'required|in_list[kesehatan_mental,karier,tes_minat]',
            'judul'    => 'required|max_length[255]',
            'konten'   => 'permit_empty|max_length[20000]',
            'link_eksternal' => 'permit_empty|valid_url_strict|max_length[255]',
            'deadline' => 'permit_empty|valid_date',
            'status'   => 'permit_empty|in_list[Draft,Published]',
        ];

        $file = $this->request->getFile('foto');
        if ($file !== null && $file->isValid() && !$file->hasMoved()) {
            $rules['foto'] = 'is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]';
        }

        return $rules;
    }

    private function findOrFail($id): ?array
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->artikelModel->find((int) $id);
    }

    private function handleUploadFoto(): ?string
    {
        $file = $this->request->getFile('foto');

        if ($file === null || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $targetDir = FCPATH . 'uploads/bk_artikel';

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

    private function generateUniqueSlug(string $judul, ?int $excludeId = null): string
    {
        $slugAsli = url_title($judul, '-', true);

        if ($slugAsli === '') {
            $slugAsli = 'artikel-bk-' . time();
        }

        $slug    = $slugAsli;
        $counter = 1;

        while (true) {
            $builder = $this->artikelModel->where('slug', $slug);
            if ($excludeId !== null) {
                $builder->where('id_artikel !=', $excludeId);
            }

            if (!$builder->first()) {
                break;
            }

            $slug = $slugAsli . '-' . $counter;
            $counter++;

            if ($counter > 1000) {
                $slug = $slugAsli . '-' . uniqid();
                break;
            }
        }

        return $slug;
    }
}