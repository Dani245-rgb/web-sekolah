<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JurusanModel;
use App\Services\FileUploadService;
use App\Services\SlugService;
use Config\Database;
use Throwable;

class Jurusan extends BaseController
{
    private const FOLDER_FOTO = 'jurusan';

    protected JurusanModel $jurusanModel;
    protected FileUploadService $uploader;
    protected SlugService $slugService;

    public function __construct()
    {
        $this->jurusanModel = new JurusanModel();
        $this->uploader     = service('fileUploadService');
        $this->slugService  = service('slugService');
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
        if (!$this->validate($this->aturanValidasi())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaJurusan = $this->normalisasiNama($this->request->getPost('nama_jurusan'));

        if ($namaJurusan === '') {
            return redirect()->back()->withInput()->with('error', 'Nama jurusan tidak boleh kosong/hanya spasi.');
        }

        if ($this->namaSudahDipakai($namaJurusan)) {
            return redirect()->back()->withInput()->with('errors', ['nama_jurusan' => 'Nama jurusan ini sudah terdaftar.']);
        }

        $db = Database::connect();
        $db->transStart();

        $fotoBaru = null;

        try {
            $insertData = [
                'nama_jurusan'  => $namaJurusan,
                'slug'          => $this->slugService->buatUnik($namaJurusan, 'jurusan', 'id_jurusan', null, 'jurusan'),
                'deskripsi'     => $this->request->getPost('deskripsi'),
                'kompetensi'    => $this->request->getPost('kompetensi'),
                'prospek_kerja' => $this->request->getPost('prospek_kerja'),
            ];

            $fotoBaru = $this->uploader->simpan($this->request->getFile('foto'), self::FOLDER_FOTO);
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
            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            log_message('error', 'Gagal simpan Jurusan: ' . $e->getMessage());

            $pesan = $e instanceof \InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal menyimpan data. Silakan coba lagi.';

            return redirect()->back()->withInput()->with('error', $pesan);
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

        if (!$this->validate($this->aturanValidasi((int) $id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaJurusan = $this->normalisasiNama($this->request->getPost('nama_jurusan'));

        if ($namaJurusan === '') {
            return redirect()->back()->withInput()->with('error', 'Nama jurusan tidak boleh kosong/hanya spasi.');
        }

        if ($this->namaSudahDipakai($namaJurusan, (int) $id)) {
            return redirect()->back()->withInput()->with('errors', ['nama_jurusan' => 'Nama jurusan ini sudah terdaftar.']);
        }

        $db = Database::connect();
        $db->transStart();

        $fotoBaru = null;
        $fotoLama = $item['foto'] ?? null;

        try {
            $updateData = [
                'nama_jurusan'  => $namaJurusan,
                'deskripsi'     => $this->request->getPost('deskripsi'),
                'kompetensi'    => $this->request->getPost('kompetensi'),
                'prospek_kerja' => $this->request->getPost('prospek_kerja'),
            ];

            if ($namaJurusan !== $item['nama_jurusan']) {
                $updateData['slug'] = $this->slugService->buatUnik($namaJurusan, 'jurusan', 'id_jurusan', (int) $id, 'jurusan');
            }

            $fotoBaru = $this->uploader->simpan($this->request->getFile('foto'), self::FOLDER_FOTO);
            if ($fotoBaru) {
                $updateData['foto'] = $fotoBaru;
            }

            $this->jurusanModel->update($id, $updateData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            // Baru hapus foto lama SETELAH transaksi sukses, biar aman kalau gagal di tengah
            if ($fotoBaru) {
                $this->uploader->hapus($fotoLama, self::FOLDER_FOTO);
            }
        } catch (Throwable $e) {
            $db->transRollback();

            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            log_message('error', 'Gagal update Jurusan: ' . $e->getMessage());

            $pesan = $e instanceof \InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal memperbarui data. Silakan coba lagi.';

            return redirect()->back()->withInput()->with('error', $pesan);
        }

        return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->findOrFail($id);
        if ($item === null) {
            return redirect()->to('/admin/jurusan')->with('error', 'Jurusan tidak ditemukan.');
        }

        try {
            $this->jurusanModel->delete($id);
            $this->uploader->hapus($item['foto'] ?? null, self::FOLDER_FOTO);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Jurusan: ' . $e->getMessage());
            return redirect()->to('/admin/jurusan')->with('error', 'Gagal menghapus data. Silakan coba lagi.');
        }

        return redirect()->to('/admin/jurusan')->with('success', 'Jurusan berhasil dihapus.');
    }

    /**
     * Rapikan nama: buang spasi di pinggir dan ubah spasi ganda jadi satu.
     */
    private function normalisasiNama($nama): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $nama));
    }

    private function namaSudahDipakai(string $nama, ?int $excludeId = null): bool
    {
        $builder = $this->jurusanModel->withDeleted()->where('nama_jurusan', $nama);

        if ($excludeId !== null) {
            $builder->where('id_jurusan !=', $excludeId);
        }

        return $builder->first() !== null;
    }

    /**
     * Aturan validasi form jurusan, dipakai bersama oleh store() dan update().
     * $excludeId diisi saat update supaya nama jurusan tidak dianggap bentrok dengan dirinya sendiri.
     */
    private function aturanValidasi(?int $excludeId = null): array
    {
        $uniqueNama = $excludeId === null
            ? 'is_unique[jurusan.nama_jurusan]'
            : "is_unique[jurusan.nama_jurusan,id_jurusan,{$excludeId}]";

        return [
            'nama_jurusan'  => "required|max_length[100]|{$uniqueNama}",
            'singkatan'     => 'permit_empty|max_length[20]',
            'deskripsi'     => 'permit_empty',
            'kompetensi'    => 'permit_empty',
            'prospek_kerja' => 'permit_empty',
            // Foto sengaja tidak divalidasi di sini: tipe dan ukuran dicek di FileUploadService.
        ];
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
}
