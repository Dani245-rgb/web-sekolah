<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BkArtikelModel;
use App\Services\FileUploadService;
use App\Services\SlugService;
use Config\Database;
use Throwable;

class BkArtikel extends BaseController
{
    private const FOLDER_FOTO = 'bk_artikel';

    protected BkArtikelModel $artikelModel;
    protected FileUploadService $uploader;
    protected SlugService $slugService;

    protected array $kategoriValid = ['kesehatan_mental', 'karier', 'tes_minat'];

    public function __construct()
    {
        helper('teks');

        $this->artikelModel = new BkArtikelModel();
        $this->uploader     = service('fileUploadService');
        $this->slugService  = service('slugService');
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
        if (!$this->validate($this->baseRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = rapikan_teks($this->request->getPost('judul'));

        if ($judul === '') {
            return redirect()->back()->withInput()->with('error', 'Judul tidak boleh kosong/hanya spasi.');
        }

        if ($this->judulSudahDipakai($judul)) {
            return redirect()->back()->withInput()->with('errors', ['judul' => 'Judul artikel ini sudah ada.']);
        }

        $db = Database::connect();
        $db->transStart();

        $fotoBaru = null;

        try {
            $insertData = [
                'kategori'        => $this->request->getPost('kategori'),
                'judul'           => $judul,
                'slug'            => $this->slugService->buatUnik($judul, 'bk_artikel', 'id_artikel', null, 'artikel-bk'),
                'konten'          => $this->request->getPost('konten'),
                'link_eksternal'  => $this->request->getPost('link_eksternal') ?: null,
                'deadline'        => $this->request->getPost('deadline') ?: null,
                'status'          => $this->request->getPost('status') ?: 'Draft',
            ];

            $fotoBaru = $this->uploader->simpan($this->request->getFile('foto'), self::FOLDER_FOTO);
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

            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            log_message('error', 'Gagal simpan Artikel BK: ' . $e->getMessage());

            $pesan = $e instanceof \InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal menyimpan data. Silakan coba lagi.';

            return redirect()->back()->withInput()->with('error', $pesan);
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

        if (!$this->validate($this->baseRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = rapikan_teks($this->request->getPost('judul'));

        if ($judul === '') {
            return redirect()->back()->withInput()->with('error', 'Judul tidak boleh kosong/hanya spasi.');
        }

        if ($this->judulSudahDipakai($judul, (int) $id)) {
            return redirect()->back()->withInput()->with('errors', ['judul' => 'Judul artikel ini sudah ada.']);
        }

        $db = Database::connect();
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
                $updateData['slug'] = $this->slugService->buatUnik($judul, 'bk_artikel', 'id_artikel', (int) $id, 'artikel-bk');
            }

            $fotoBaru = $this->uploader->simpan($this->request->getFile('foto'), self::FOLDER_FOTO);
            if ($fotoBaru) {
                $updateData['foto'] = $fotoBaru;
            }

            $this->artikelModel->update($id, $updateData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            // Foto lama baru dihapus SETELAH transaksi sukses
            if ($fotoBaru) {
                $this->uploader->hapus($fotoLama, self::FOLDER_FOTO);
            }
        } catch (Throwable $e) {
            $db->transRollback();

            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            log_message('error', 'Gagal update Artikel BK: ' . $e->getMessage());

            $pesan = $e instanceof \InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal memperbarui data. Silakan coba lagi.';

            return redirect()->back()->withInput()->with('error', $pesan);
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
            $this->uploader->hapus($item['foto'] ?? null, self::FOLDER_FOTO);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Artikel BK: ' . $e->getMessage());
            return redirect()->to('/admin/bk-artikel')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/bk-artikel')->with('success', 'Artikel berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function baseRules(): array
    {
        return [
            'kategori'       => 'required|in_list[kesehatan_mental,karier,tes_minat]',
            'judul'          => 'required|max_length[255]',
            'konten'         => 'permit_empty|max_length[20000]',
            'link_eksternal' => 'permit_empty|valid_url_strict|max_length[255]',
            'deadline'       => 'permit_empty|valid_date',
            'status'         => 'permit_empty|in_list[Draft,Published]',
            // Foto sengaja tidak divalidasi di sini: tipe dan ukuran dicek di FileUploadService.
        ];
    }

    private function judulSudahDipakai(string $judul, ?int $excludeId = null): bool
    {
        $builder = $this->artikelModel->where('judul', $judul);

        if ($excludeId !== null) {
            $builder->where('id_artikel !=', $excludeId);
        }

        return $builder->first() !== null;
    }

    private function findOrFail($id): ?array
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->artikelModel->find((int) $id);
    }
}