<?php

namespace App\Services;

use App\Models\PesanKontakModel;
use InvalidArgumentException;
use RuntimeException;

/**
 * Logika bisnis pesan kontak sisi admin: daftar, lihat detail (menandai dibaca), hapus.
 * Sisi publik (kirim pesan) ada di KontakService.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * RuntimeException         = kegagalan sistem (controller yang log + tampilkan pesan umum).
 */
class KontakAdminService
{
    public const TIDAK_DITEMUKAN = 'Pesan tidak ditemukan.';

    private const BELUM_DIBACA  = 'Belum Dibaca';
    private const SUDAH_DIBACA  = 'Sudah Dibaca';

    public function __construct(
        private PesanKontakModel $kontakModel,
    ) {
    }

    public function daftar(): array
    {
        return $this->kontakModel->daftarTerbaru();
    }

    public function ambil(int $id): array
    {
        $pesan = $this->kontakModel->find($id);

        if (!$pesan) {
            throw new InvalidArgumentException(self::TIDAK_DITEMUKAN);
        }

        return $pesan;
    }

    /**
     * Ambil pesan untuk ditampilkan. Kalau masih "Belum Dibaca", ditandai "Sudah Dibaca".
     * Kalau menandai gagal, pesan tetap ditampilkan dengan status lama (tidak sampai error).
     */
    public function lihat(int $id): array
    {
        $pesan = $this->ambil($id);

        if (($pesan['status'] ?? null) === self::BELUM_DIBACA && $this->kontakModel->tandaiSudahDibaca($id)) {
            $pesan['status'] = self::SUDAH_DIBACA;
        }

        return $pesan;
    }

    public function hapus(int $id): void
    {
        $this->ambil($id);

        if ($this->kontakModel->delete($id) === false) {
            throw new RuntimeException('Pesan kontak gagal dihapus (id ' . $id . ').');
        }
    }
}