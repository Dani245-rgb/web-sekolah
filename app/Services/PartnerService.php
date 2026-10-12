<?php

namespace App\Services;

use App\Models\PartnerModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Aturan bisnis Partner: nama unik, slug, upload foto, dan urutan hapus file.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class PartnerService
{
    private const FOLDER = 'partner'; // public/uploads/partner

    public function __construct(
        private PartnerModel $model,
        private FileUploadService $uploader,
        private SlugService $slugService,
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

        $slug     = $this->slugService->buatUnik($nama, 'partner', 'id', null, 'partner');
        $namaFoto = $this->unggah($foto);

        if ($namaFoto === null) {
            throw new InvalidArgumentException('Foto gagal diupload.');
        }

        $berhasil = $this->model->insert([
            'nama'      => $nama,
            'slug'      => $slug,
            'foto'      => $namaFoto,
            'deskripsi' => $input['deskripsi'] ?? null,
            'status'    => $input['status'] ?? null,
        ]);

        if (!$berhasil) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->uploader->hapus($namaFoto, self::FOLDER);

            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan partner.'));
        }
    }

    public function ubah(int $id, array $input, ?UploadedFile $foto = null): void
    {
        $partner = $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $nama = rapikan_teks((string) ($input['nama'] ?? ''));
        $this->pastikanNamaBoleh($nama, $id);

        $data = [
            'nama'      => $nama,
            'slug'      => $this->slugService->buatUnik($nama, 'partner', 'id', $id, 'partner'),
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

            throw new InvalidArgumentException($this->pesanModel('Gagal memperbarui partner.'));
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->uploader->hapus($partner['foto'] ?? null, self::FOLDER);
        }
    }

    public function hapus(int $id): void
    {
        $partner = $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $this->model->delete($id);

        // File dihapus SETELAH record DB terhapus
        $this->uploader->hapus($partner['foto'] ?? null, self::FOLDER);
    }

    // ================= Helper privat =================

    private function pastikanNamaBoleh(string $nama, ?int $excludeId = null): void
    {
        if ($this->model->namaSudahAda($nama, $excludeId)) {
            throw new InvalidArgumentException('Nama partner sudah dipakai.');
        }
    }

    /**
     * Simpan foto. Null kalau tidak ada file baru. Pesan InvalidArgumentException
     * dari uploader (tipe/ukuran salah) diteruskan apa adanya ke user.
     */
    private function unggah(?UploadedFile $foto): ?string
    {
        try {
            return $this->uploader->simpanKompres($foto, self::FOLDER);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload foto Partner: ' . $e->getMessage());

            throw new InvalidArgumentException('Gagal menyimpan foto. Silakan coba lagi.');
        }
    }

    private function pesanModel(string $cadangan): string
    {
        $errors = $this->model->errors();

        return $errors ? implode('; ', $errors) : $cadangan;
    }
}