<?php

namespace App\Services;

use App\Models\AnggotaOrganisasiModel;
use App\Models\OrganisasiModel;
use InvalidArgumentException;

/**
 * Aturan bisnis Organisasi: nama unik dan penghapusan organisasi beserta foto anggotanya.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class OrganisasiService
{
    private const FOLDER_ANGGOTA = 'organisasi'; // public/uploads/organisasi

    public function __construct(
        private OrganisasiModel $model,
        private AnggotaOrganisasiModel $anggotaModel,
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

    public function tambah(array $input): void
    {
        $nama = rapikan_teks((string) ($input['nama'] ?? ''));
        $this->pastikanNamaBoleh($nama);

        $berhasil = $this->model->insert([
            'nama'      => $nama,
            'deskripsi' => $input['deskripsi'] ?? null,
            'status'    => $input['status'] ?? null,
        ]);

        if (!$berhasil) {
            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan organisasi.'));
        }
    }

    public function ubah(int $id, array $input): void
    {
        $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $nama = rapikan_teks((string) ($input['nama'] ?? ''));
        $this->pastikanNamaBoleh($nama, $id);

        $berhasil = $this->model->update($id, [
            'nama'      => $nama,
            'deskripsi' => $input['deskripsi'] ?? null,
            'status'    => $input['status'] ?? null,
        ]);

        if (!$berhasil) {
            throw new InvalidArgumentException($this->pesanModel('Gagal memperbarui organisasi.'));
        }
    }

    /**
     * Hapus organisasi. Record anggota ikut terhapus otomatis (foreign key CASCADE),
     * tapi file foto mereka tidak, jadi foto dicatat dulu lalu dihapus setelah record terhapus.
     */
    public function hapus(int $id): void
    {
        $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $anggota = $this->anggotaModel->getByOrganisasi($id);

        $this->model->delete($id);

        // File dihapus SETELAH record DB terhapus
        foreach ($anggota as $a) {
            $this->uploader->hapus($a['foto'] ?? null, self::FOLDER_ANGGOTA);
        }
    }

    // ================= Helper privat =================

    private function pastikanNamaBoleh(string $nama, ?int $excludeId = null): void
    {
        if ($this->model->namaSudahAda($nama, $excludeId)) {
            throw new InvalidArgumentException('Nama organisasi sudah dipakai.');
        }
    }

    private function pesanModel(string $cadangan): string
    {
        $errors = $this->model->errors();

        return $errors ? implode('; ', $errors) : $cadangan;
    }
}