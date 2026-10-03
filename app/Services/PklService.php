<?php

namespace App\Services;

use App\Models\GuruModel;
use App\Models\PklPenempatanModel;
use App\Models\SiswaModel;
use InvalidArgumentException;

/**
 * Aturan bisnis penempatan PKL: normalisasi input, validasi, aturan tanggal,
 * dan "satu penempatan Aktif per siswa" (aturan itu sendiri ada di PklPenempatanModel::cekAturan()).
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class PklService
{
    public function __construct(
        private PklPenempatanModel $model,
        private SiswaModel $siswaModel,
        private GuruModel $guruModel,
    ) {
        helper('teks');
    }

    /**
     * @return array{daftar: array, filter: array{keyword: ?string, status: ?string}}
     */
    public function daftar($keyword = null, $status = null): array
    {
        $filter = [
            'keyword' => is_string($keyword) && trim($keyword) !== '' ? trim($keyword) : null,
            'status'  => in_array($status, PklPenempatanModel::STATUS, true) ? $status : null,
        ];

        return [
            'daftar' => $this->model->getAllWithRelasi($filter),
            'filter' => $filter,
        ];
    }

    /**
     * Data untuk form tambah/edit: daftar siswa, guru aktif, dan penempatan (null kalau tambah).
     */
    public function dataForm(?array $penempatan): array
    {
        return [
            'siswaList'  => $this->siswaModel->getAll()->findAll(),
            'guruList'   => $this->guruModel->getAktif(),
            'penempatan' => $penempatan,
        ];
    }

    public function cari($id): ?array
    {
        return $this->model->find($id);
    }

    public function tambah(array $input): void
    {
        $data = $this->rapikanInput($input);

        $this->pastikanLolos($data);

        if (!$this->model->insert($data)) {
            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan penempatan PKL.'));
        }
    }

    public function ubah(int $id, array $input): void
    {
        $this->cari($id) ?? throw new InvalidArgumentException('Data penempatan tidak ditemukan.');

        $data = $this->rapikanInput($input);

        $this->pastikanLolos($data, $id);

        if (!$this->model->update($id, $data)) {
            throw new InvalidArgumentException($this->pesanModel('Gagal memperbarui penempatan PKL.'));
        }
    }

    /**
     * Hapus penempatan. Jurnal ikut terhapus otomatis (ON DELETE CASCADE).
     */
    public function hapus(int $id): void
    {
        $this->cari($id) ?? throw new InvalidArgumentException('Data penempatan tidak ditemukan.');

        $this->model->delete($id);
    }

    // ================= Helper privat =================

    /**
     * Rapikan input form. Guru pembimbing dan tanggal selesai yang kosong disimpan NULL.
     */
    private function rapikanInput(array $raw): array
    {
        return [
            'id_siswa'                  => $raw['id_siswa'] ?? null,
            'id_guru_pembimbing'        => ($raw['id_guru_pembimbing'] ?? null) ?: null,
            'nama_perusahaan'           => rapikan_teks((string) ($raw['nama_perusahaan'] ?? '')),
            'alamat_perusahaan'         => $raw['alamat_perusahaan'] ?? null,
            'nama_pembimbing_industri'  => rapikan_teks((string) ($raw['nama_pembimbing_industri'] ?? '')),
            'no_hp_pembimbing_industri' => rapikan_teks((string) ($raw['no_hp_pembimbing_industri'] ?? '')),
            'tanggal_mulai'             => $raw['tanggal_mulai'] ?? null,
            'tanggal_selesai'           => ($raw['tanggal_selesai'] ?? null) ?: null,
            'status'                    => $raw['status'] ?? null,
            'catatan'                   => $raw['catatan'] ?? null,
        ];
    }

    /**
     * Validasi biasa dulu, lalu aturan bisnis. Lempar InvalidArgumentException kalau tidak lolos.
     */
    private function pastikanLolos(array $data, ?int $kecualiId = null): void
    {
        if (!$this->model->validate($data)) {
            $errors = $this->model->errors();

            throw new InvalidArgumentException($errors ? implode('; ', $errors) : 'Data penempatan tidak valid.');
        }

        $pesan = $this->model->cekAturan($data, $kecualiId);

        if ($pesan !== null) {
            throw new InvalidArgumentException($pesan);
        }
    }

    private function pesanModel(string $cadangan): string
    {
        $errors = $this->model->errors();

        return $errors ? implode('; ', $errors) : $cadangan;
    }
}