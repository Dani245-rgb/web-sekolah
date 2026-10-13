<?php

namespace App\Services;

use App\Models\GaleriModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Aturan bisnis Galeri: upload foto dan urutan hapus file.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class GaleriService
{
    private const FOLDER = 'galeri'; // public/uploads/galeri

    public function __construct(
        private GaleriModel $model,
        private FileUploadService $uploader,
    ) {
        helper('teks');
    }

    public function daftar(): array
    {
        return $this->model->getSemuaTerbaru();
    }

    public function cari($id): ?array
    {
        return $this->model->find($id);
    }

    public function tambah(array $input, ?UploadedFile $foto): void
    {
        $namaFoto = $this->unggah($foto);

        if ($namaFoto === null) {
            throw new InvalidArgumentException('Foto gagal diupload.');
        }

        $berhasil = $this->model->insert([
            'judul'     => rapikan_teks((string) ($input['judul'] ?? '')),
            'foto'      => $namaFoto,
            'kategori'  => $input['kategori'] ?? null,
            'deskripsi' => $input['deskripsi'] ?? null,
            'status'    => $input['status'] ?? null,
        ]);

        if (!$berhasil) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->uploader->hapus($namaFoto, self::FOLDER);

            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan foto.'));
        }
    }

    public function ubah(int $id, array $input, ?UploadedFile $foto = null): void
    {
        $galeri = $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $data = [
            'judul'     => rapikan_teks((string) ($input['judul'] ?? '')),
            'kategori'  => $input['kategori'] ?? null,
            'deskripsi' => $input['deskripsi'] ?? null,
            'status'    => $input['status'] ?? null,
        ];

        // Foto baru (opsional). Kalau tidak diisi, foto lama dipertahankan.
        $fotoBaru = $this->unggah($foto);

        if ($fotoBaru !== null) {
            $data['foto'] = $fotoBaru;
        }

        if (!$this->model->update($id, $data)) {
            $this->uploader->hapus($fotoBaru, self::FOLDER);

            throw new InvalidArgumentException($this->pesanModel('Gagal memperbarui foto.'));
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->uploader->hapus($galeri['foto'] ?? null, self::FOLDER);
        }
    }

    public function hapus(int $id): void
    {
        $galeri = $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $this->model->delete($id);

        // File dihapus SETELAH record DB terhapus
        $this->uploader->hapus($galeri['foto'] ?? null, self::FOLDER);
    }

    // ================= Helper privat =================

    /**
     * Simpan foto. Null kalau tidak ada file baru. Pesan InvalidArgumentException
     * dari uploader (tipe/ukuran salah) diteruskan apa adanya ke user.
     */
    private function unggah(?UploadedFile $foto): ?string
    {
        try {
            return $this->uploader->simpanKompres(
                $foto,
                self::FOLDER,
                FileUploadService::MIME_GAMBAR,
                FileUploadService::MAKS_BYTES,
                1600
            );
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload foto Galeri: ' . $e->getMessage());

            throw new InvalidArgumentException('Gagal menyimpan foto. Silakan coba lagi.');
        }
    }

    private function pesanModel(string $cadangan): string
    {
        $errors = $this->model->errors();

        return $errors ? implode('; ', $errors) : $cadangan;
    }
}
