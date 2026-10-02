<?php

namespace App\Services;

use App\Models\EkstrakurikulerModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Aturan bisnis Ekstrakurikuler: nama unik, upload foto, dan urutan hapus file.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class EkstrakurikulerService
{
    private const FOLDER = 'ekstrakurikuler'; // public/uploads/ekstrakurikuler

    public function __construct(
        private EkstrakurikulerModel $model,
        private FileUploadService $uploader,
    ) {
        helper('teks');
    }

    public function daftar(): array
    {
        return $this->model->getSemuaUrut();
    }

    public function cari($id): ?array
    {
        return $this->model->find($id);
    }

    public function tambah(array $input, ?UploadedFile $foto): void
    {
        $nama = rapikan_teks((string) ($input['nama'] ?? ''));
        $this->pastikanNamaBoleh($nama);

        $namaFoto = $this->unggah($foto);

        if ($namaFoto === null) {
            throw new InvalidArgumentException('Foto gagal diupload.');
        }

        $berhasil = $this->model->insert([
            'nama'      => $nama,
            'foto'      => $namaFoto,
            'deskripsi' => $input['deskripsi'] ?? null,
            'status'    => $input['status'] ?? null,
        ]);

        if (!$berhasil) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->uploader->hapus($namaFoto, self::FOLDER);

            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan ekstrakurikuler.'));
        }
    }

    public function ubah(int $id, array $input, ?UploadedFile $foto = null): void
    {
        $ekskul = $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $nama = rapikan_teks((string) ($input['nama'] ?? ''));
        $this->pastikanNamaBoleh($nama, $id);

        $data = [
            'nama'      => $nama,
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

            throw new InvalidArgumentException($this->pesanModel('Gagal memperbarui ekstrakurikuler.'));
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->uploader->hapus($ekskul['foto'] ?? null, self::FOLDER);
        }
    }

    public function hapus(int $id): void
    {
        $ekskul = $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $this->model->delete($id);

        // File dihapus SETELAH record DB terhapus
        $this->uploader->hapus($ekskul['foto'] ?? null, self::FOLDER);
    }

    // ================= Helper privat =================

    private function pastikanNamaBoleh(string $nama, ?int $excludeId = null): void
    {
        if ($this->model->namaSudahAda($nama, $excludeId)) {
            throw new InvalidArgumentException('Nama ekstrakurikuler sudah dipakai.');
        }
    }

    /**
     * Simpan foto. Null kalau tidak ada file baru. Pesan InvalidArgumentException
     * dari uploader (tipe/ukuran salah) diteruskan apa adanya ke user.
     */
    private function unggah(?UploadedFile $foto): ?string
    {
        try {
            return $this->uploader->simpan($foto, self::FOLDER);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload foto Ekstrakurikuler: ' . $e->getMessage());

            throw new InvalidArgumentException('Gagal menyimpan foto. Silakan coba lagi.');
        }
    }

    private function pesanModel(string $cadangan): string
    {
        $errors = $this->model->errors();

        return $errors ? implode('; ', $errors) : $cadangan;
    }
}