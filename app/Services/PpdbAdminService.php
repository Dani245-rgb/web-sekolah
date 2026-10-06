<?php

namespace App\Services;

use App\Models\PendaftarPpdbModel;
use App\Models\PengaturanPpdbModel;
use InvalidArgumentException;
use RuntimeException;

/**
 * Logika bisnis PPDB sisi admin: daftar pendaftar, ubah status, hapus, dan pengaturan pendaftaran.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * RuntimeException         = kegagalan sistem (controller yang log + tampilkan pesan umum).
 */
class PpdbAdminService
{
    public function __construct(
        private PendaftarPpdbModel $pendaftarModel,
        private PengaturanPpdbModel $pengaturanModel,
    ) {
    }

    public function daftar(): array
    {
        return $this->pendaftarModel->daftarTerbaru();
    }

    public function ambil(int $id): array
    {
        $pendaftar = $this->pendaftarModel->find($id);

        if (!$pendaftar) {
            throw new InvalidArgumentException('Data tidak ditemukan.');
        }

        return $pendaftar;
    }

    public function ubahStatus(int $id, string $status): void
    {
        $this->ambil($id);

        if (!in_array($status, PendaftarPpdbModel::STATUS, true)) {
            throw new InvalidArgumentException('Status tidak valid.');
        }

        if (!$this->pendaftarModel->ubahStatus($id, $status)) {
            throw new RuntimeException('Status pendaftar PPDB gagal disimpan (id ' . $id . ').');
        }
    }

    public function hapus(int $id): void
    {
        $this->ambil($id);

        if ($this->pendaftarModel->delete($id) === false) {
            throw new RuntimeException('Pendaftar PPDB gagal dihapus (id ' . $id . ').');
        }
    }

    public function pengaturan(): array
    {
        return $this->pengaturanModel->getSetting();
    }

    /**
     * @param array{status?: mixed, pesan_tutup?: mixed, tahun_ajaran?: mixed} $input
     */
    public function simpanPengaturan(array $input): void
    {
        helper('teks');

        $status = (string) ($input['status'] ?? '');

        if ($status === '') {
            throw new InvalidArgumentException('Status pendaftaran wajib diisi.');
        }

        if (!in_array($status, PengaturanPpdbModel::STATUS, true)) {
            throw new InvalidArgumentException('Status pendaftaran tidak valid.');
        }

        $pesanTutup = $input['pesan_tutup'] ?? null;

        if ($pesanTutup !== null && mb_strlen((string) $pesanTutup) > 255) {
            throw new InvalidArgumentException('Pesan tutup maksimal 255 karakter.');
        }

        $tahunAjaran = rapikan_teks((string) ($input['tahun_ajaran'] ?? ''));

        if (mb_strlen($tahunAjaran) > 20) {
            throw new InvalidArgumentException('Tahun ajaran maksimal 20 karakter.');
        }

        // Pendaftaran tidak boleh dibuka tanpa tahun ajaran, supaya data pendaftar tercatat lengkap
        if ($status === 'buka' && $tahunAjaran === '') {
            throw new InvalidArgumentException('Isi tahun ajaran dulu sebelum membuka pendaftaran.');
        }

        $berhasil = $this->pengaturanModel->simpan([
            'status'       => $status,
            'pesan_tutup'  => $pesanTutup,
            'tahun_ajaran' => $tahunAjaran !== '' ? $tahunAjaran : null,
        ]);

        if (!$berhasil) {
            throw new RuntimeException('Pengaturan PPDB gagal disimpan.');
        }
    }
}