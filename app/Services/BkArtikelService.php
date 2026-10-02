<?php

namespace App\Services;

use App\Models\BkArtikelModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Aturan bisnis Artikel BK: filter kategori, judul unik, slug, transaksi, foto.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class BkArtikelService
{
    private const FOLDER_FOTO = 'bk_artikel';

    public function __construct(
        private BkArtikelModel $model,
        private FileUploadService $uploader,
        private SlugService $slugService,
        private BaseConnection $db,
    ) {
        helper('teks');
    }

    /**
     * Kategori yang tidak dikenal diabaikan (tidak memfilter).
     */
    public function daftar(?string $kategori = null): array
    {
        $valid = ($kategori !== null && in_array($kategori, array_keys(BkArtikelModel::KATEGORI), true))
            ? $kategori
            : null;

        return $this->model->getDaftarAdmin($valid);
    }

    public function cari($id): ?array
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->model->find((int) $id);
    }

    public function tambah(array $input, ?UploadedFile $foto = null): void
    {
        $judul = rapikan_teks($input['judul'] ?? '');
        $this->pastikanJudulBoleh($judul);

        $fotoBaru = null;
        $this->db->transStart();

        try {
            $data         = $this->susunData($judul, $input);
            $data['slug'] = $this->slugService->buatUnik($judul, 'bk_artikel', 'id_artikel', null, 'artikel-bk');

            $fotoBaru = $this->uploader->simpan($foto, self::FOLDER_FOTO);
            if ($fotoBaru) {
                $data['foto'] = $fotoBaru;
            }

            if ($this->model->insert($data) === false) {
                throw new RuntimeException('Insert artikel BK gagal.');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Transaksi database gagal.');
            }
        } catch (Throwable $e) {
            $this->db->transRollback();
            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            throw $e;
        }
    }

    public function ubah(int $id, array $input, ?UploadedFile $foto = null): void
    {
        $item = $this->cari($id) ?? throw new InvalidArgumentException('Artikel tidak ditemukan.');

        $judul = rapikan_teks($input['judul'] ?? '');
        $this->pastikanJudulBoleh($judul, $id);

        $fotoLama = $item['foto'] ?? null;
        $fotoBaru = null;
        $this->db->transStart();

        try {
            $data = $this->susunData($judul, $input);

            if ($judul !== $item['judul']) {
                $data['slug'] = $this->slugService->buatUnik($judul, 'bk_artikel', 'id_artikel', $id, 'artikel-bk');
            }

            $fotoBaru = $this->uploader->simpan($foto, self::FOLDER_FOTO);
            if ($fotoBaru) {
                $data['foto'] = $fotoBaru;
            }

            if ($this->model->update($id, $data) === false) {
                throw new RuntimeException('Update artikel BK gagal.');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Transaksi database gagal.');
            }

            // Foto lama baru dihapus SETELAH transaksi sukses
            if ($fotoBaru) {
                $this->uploader->hapus($fotoLama, self::FOLDER_FOTO);
            }
        } catch (Throwable $e) {
            $this->db->transRollback();
            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            throw $e;
        }
    }

    public function hapus(int $id): void
    {
        $item = $this->cari($id) ?? throw new InvalidArgumentException('Artikel tidak ditemukan.');

        $this->model->delete($id);
        $this->uploader->hapus($item['foto'] ?? null, self::FOLDER_FOTO);
    }

    private function pastikanJudulBoleh(string $judul, ?int $excludeId = null): void
    {
        if ($judul === '') {
            throw new InvalidArgumentException('Judul tidak boleh kosong/hanya spasi.');
        }

        if ($this->model->judulSudahDipakai($judul, $excludeId)) {
            throw new InvalidArgumentException('Judul artikel ini sudah ada.');
        }
    }

    private function susunData(string $judul, array $input): array
    {
        return [
            'kategori'       => $input['kategori'] ?? null,
            'judul'          => $judul,
            'konten'         => $input['konten'] ?? null,
            'link_eksternal' => ($input['link_eksternal'] ?? null) ?: null,
            'deadline'       => ($input['deadline'] ?? null) ?: null,
            'status'         => ($input['status'] ?? null) ?: 'Draft',
        ];
    }
}