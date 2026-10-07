<?php

namespace App\Services;

use App\Models\PengumumanModel;
use InvalidArgumentException;
use RuntimeException;

/**
 * Logika bisnis pengumuman (admin): validasi, judul unik, slug, simpan, hapus.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * RuntimeException         = kegagalan sistem (controller yang log + tampilkan pesan umum).
 */
class PengumumanService
{
    public const TIDAK_DITEMUKAN = 'Data tidak ditemukan.';

    private const STATUS = ['Published', 'Draft'];

    public function __construct(
        private PengumumanModel $pengumumanModel,
        private SlugService $slugService,
    ) {
    }

    public function daftar(): array
    {
        return $this->pengumumanModel->daftarTerbaru();
    }

    public function ambil(int $id): array
    {
        $pengumuman = $this->pengumumanModel->find($id);

        if (!$pengumuman) {
            throw new InvalidArgumentException(self::TIDAK_DITEMUKAN);
        }

        return $pengumuman;
    }

    /**
     * @param array{judul?: mixed, isi?: mixed, tanggal_publish?: mixed, status?: mixed} $input
     */
    public function tambah(array $input): int
    {
        $data = $this->bersihkan($input);

        if ($this->pengumumanModel->judulSudahAda($data['judul'])) {
            throw new InvalidArgumentException('Judul pengumuman sudah dipakai.');
        }

        $data['slug'] = $this->slugService->buatUnik($data['judul'], 'pengumuman', 'id', null, 'pengumuman');

        $id = $this->pengumumanModel->insert($data);

        if (!$id) {
            $this->gagalSimpan('Pengumuman gagal ditambahkan.');
        }

        return (int) $id;
    }

    /**
     * @param array{judul?: mixed, isi?: mixed, tanggal_publish?: mixed, status?: mixed} $input
     */
    public function ubah(int $id, array $input): void
    {
        $this->ambil($id);

        $data = $this->bersihkan($input);

        if ($this->pengumumanModel->judulSudahAda($data['judul'], $id)) {
            throw new InvalidArgumentException('Judul pengumuman sudah dipakai.');
        }

        $data['slug'] = $this->slugService->buatUnik($data['judul'], 'pengumuman', 'id', $id, 'pengumuman');

        if (!$this->pengumumanModel->update($id, $data)) {
            $this->gagalSimpan('Pengumuman gagal diperbarui (id ' . $id . ').');
        }
    }

    public function hapus(int $id): void
    {
        $this->ambil($id);

        if ($this->pengumumanModel->delete($id) === false) {
            throw new RuntimeException('Pengumuman gagal dihapus (id ' . $id . ').');
        }
    }

    /**
     * Rapikan judul lalu validasi. Semua pesan error dikumpulkan jadi satu.
     */
    private function bersihkan(array $input): array
    {
        helper('teks');

        $judul   = rapikan_teks((string) ($input['judul'] ?? ''));
        $isi     = (string) ($input['isi'] ?? '');
        $tanggal = (string) ($input['tanggal_publish'] ?? '');
        $status  = (string) ($input['status'] ?? '');

        $errors = [];

        if ($judul === '') {
            $errors[] = 'Judul wajib diisi.';
        } elseif (mb_strlen($judul) < 3) {
            $errors[] = 'Judul minimal 3 karakter.';
        } elseif (mb_strlen($judul) > 255) {
            $errors[] = 'Judul maksimal 255 karakter.';
        }

        if (trim($isi) === '') {
            $errors[] = 'Isi pengumuman wajib diisi.';
        }

        if (trim($tanggal) === '') {
            $errors[] = 'Tanggal publish wajib diisi.';
        } elseif (strtotime($tanggal) === false) {
            $errors[] = 'Tanggal publish tidak valid.';
        }

        if (!in_array($status, self::STATUS, true)) {
            $errors[] = 'Status harus Published atau Draft.';
        }

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        return [
            'judul'           => $judul,
            'isi'             => $isi,
            'tanggal_publish' => $tanggal,
            'status'          => $status,
        ];
    }

    /**
     * Kalau model menolak karena validasinya sendiri (errors terisi), itu pesan aman.
     * Kalau errors kosong, berarti gagal di database.
     */
    private function gagalSimpan(string $pesanSistem): never
    {
        $errors = $this->pengumumanModel->errors();

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        throw new RuntimeException($pesanSistem);
    }
}