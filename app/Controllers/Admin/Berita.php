<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\BeritaModel;
use App\Services\FileUploadService;
use App\Services\SlugService;
use InvalidArgumentException;
use RuntimeException;

class Berita extends BaseController
{
    // Gambar berita disimpan di public/assets/images/berita (bukan public/uploads)
    private const FOLDER_GAMBAR = 'assets/images/berita';
    private const MIME_GAMBAR   = ['image/jpeg', 'image/png'];

    protected BeritaModel $beritaModel;
    protected AuditLogModel $auditLogModel;
    protected FileUploadService $uploader;
    protected SlugService $slugService;

    public function __construct()
    {
        helper('teks');

        $this->beritaModel   = new BeritaModel();
        $this->auditLogModel = new AuditLogModel();
        $this->uploader      = service('fileUploadService');
        $this->slugService   = service('slugService');
    }

    public function index()
    {
        $keyword = $this->request->getGet('q');

        $builder = $this->beritaModel->orderBy('created_at', 'DESC');
        if ($keyword) {
            $builder->like('judul', $keyword);
        }

        $beritaList = $builder->paginate(10);

        return view('admin/berita/index', [
            'beritaList' => $beritaList,
            'pager'      => $this->beritaModel->pager,
            'keyword'    => $keyword,
        ]);
    }

    public function create()
    {
        return view('admin/berita/form', ['berita' => null]);
    }

    public function store()
    {
        if (!$this->validate($this->aturanValidasi())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = rapikan_teks($this->request->getPost('judul'));

        if (mb_strlen($judul) < 5) {
            return redirect()->back()->withInput()->with('errors', ['judul' => 'Judul minimal 5 karakter.']);
        }

        $slug = $this->slugService->buatUnik($judul, 'berita', 'id_berita', null, 'berita');

        try {
            $namaFile = $this->uploader->simpan($this->request->getFile('gambar'), self::FOLDER_GAMBAR, self::MIME_GAMBAR);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('errors', ['gambar' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload gambar Berita: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('errors', ['gambar' => 'Gagal menyimpan gambar. Periksa permission folder.']);
        }

        $idBerita = $this->beritaModel->insert([
            'judul'           => $judul,
            'slug'            => $slug,
            'kategori'        => $this->request->getPost('kategori'),
            'gambar'          => $namaFile,
            'ringkasan'       => $this->request->getPost('ringkasan'),
            'konten'          => $this->request->getPost('konten'),
            'status'          => $this->request->getPost('status'),
            'posisi'          => $this->request->getPost('posisi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish') ?: date('Y-m-d'),
            'id_user'         => session()->get('id_user'),
        ]);

        if (!$idBerita) {
            // Insert gagal: buang gambar yang sudah terlanjur diupload
            $this->uploader->hapus($namaFile, self::FOLDER_GAMBAR);

            $errors = $this->beritaModel->errors() ?: ['db' => 'Gagal menyimpan berita.'];
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $this->catatAudit('Tambah Berita', $judul);

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $berita = $this->beritaModel->find($id);
        if (!$berita) {
            return redirect()->to('/admin/berita')->with('errors', ['404' => 'Berita tidak ditemukan.']);
        }

        return view('admin/berita/form', ['berita' => $berita]);
    }

    public function update($id)
    {
        $berita = $this->beritaModel->find($id);
        if (!$berita) {
            return redirect()->to('/admin/berita')->with('errors', ['404' => 'Berita tidak ditemukan.']);
        }

        if (!$this->validate($this->aturanValidasi())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judul = rapikan_teks($this->request->getPost('judul'));

        if (mb_strlen($judul) < 5) {
            return redirect()->back()->withInput()->with('errors', ['judul' => 'Judul minimal 5 karakter.']);
        }

        $slug = $judul !== $berita['judul']
            ? $this->slugService->buatUnik($judul, 'berita', 'id_berita', (int) $id, 'berita')
            : $berita['slug'];

        try {
            $gambarBaru = $this->uploader->simpan($this->request->getFile('gambar'), self::FOLDER_GAMBAR, self::MIME_GAMBAR);
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('errors', ['gambar' => $e->getMessage()]);
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload gambar Berita: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('errors', ['gambar' => 'Gagal menyimpan gambar. Periksa permission folder.']);
        }

        $berhasil = $this->beritaModel->update($id, [
            'judul'           => $judul,
            'slug'            => $slug,
            'kategori'        => $this->request->getPost('kategori'),
            'gambar'          => $gambarBaru ?? $berita['gambar'],
            'ringkasan'       => $this->request->getPost('ringkasan'),
            'konten'          => $this->request->getPost('konten'),
            'status'          => $this->request->getPost('status'),
            'posisi'          => $this->request->getPost('posisi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish') ?: $berita['tanggal_publish'],
        ]);

        if (!$berhasil) {
            // Update gagal: buang gambar baru, gambar lama tetap aman
            $this->uploader->hapus($gambarBaru, self::FOLDER_GAMBAR);

            $errors = $this->beritaModel->errors() ?: ['db' => 'Gagal memperbarui berita.'];
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        // Gambar lama baru dihapus SETELAH update berhasil
        if ($gambarBaru) {
            $this->uploader->hapus($berita['gambar'] ?? null, self::FOLDER_GAMBAR);
        }

        $this->catatAudit('Edit Berita', $judul);

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil diperbarui.');
    }

    public function delete($id)
    {
        $berita = $this->beritaModel->find($id);
        if (!$berita) {
            return redirect()->to('/admin/berita')->with('errors', ['404' => 'Berita tidak ditemukan.']);
        }

        $this->beritaModel->delete($id);
        $this->uploader->hapus($berita['gambar'] ?? null, self::FOLDER_GAMBAR);

        $this->catatAudit('Hapus Berita', $berita['judul']);

        return redirect()->to('/admin/berita')->with('success', 'Berita berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function aturanValidasi(): array
    {
        return [
            'judul'    => 'required|min_length[5]|max_length[200]',
            'kategori' => 'required|in_list[Akademik,Prestasi,Kegiatan,Umum]',
            'konten'   => 'required',
            'status'   => 'required|in_list[Draft,Published]',
            'posisi'   => 'required|in_list[hero,utama,biasa,populer,hits]',
            // Gambar sengaja tidak divalidasi di sini: tipe dan ukuran dicek di FileUploadService.
        ];
    }

    private function catatAudit(string $aksi, string $judul): void
    {
        $this->auditLogModel->catat(
            session()->get('id_user'),
            session()->get('nama') ?? 'Admin',
            $aksi,
            "Judul:{$judul}"
        );
    }
}