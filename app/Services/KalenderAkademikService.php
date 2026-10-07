<?php

namespace App\Services;

use App\Models\KalenderAkademikModel;
use InvalidArgumentException;
use RuntimeException;

/**
 * Logika bisnis kalender akademik (admin): validasi, urutan tanggal, simpan, hapus.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * RuntimeException         = kegagalan sistem (controller yang log + tampilkan pesan umum).
 */
class KalenderAkademikService
{
    public const TIDAK_DITEMUKAN = 'Data tidak ditemukan.';

    private const SEMESTER = ['Ganjil', 'Genap'];
    private const STATUS   = ['Published', 'Draft'];

    public function __construct(
        private KalenderAkademikModel $kalenderModel,
    ) {
    }

    public function daftar(): array
    {
        return $this->kalenderModel->daftarUrut();
    }

    public function ambil(int $id): array
    {
        $item = $this->kalenderModel->find($id);

        if (!$item) {
            throw new InvalidArgumentException(self::TIDAK_DITEMUKAN);
        }

        return $item;
    }

    public function tambah(array $input): int
    {
        $data = $this->bersihkan($input);

        $id = $this->kalenderModel->insert($data);

        if (!$id) {
            $this->gagalSimpan('Kegiatan kalender akademik gagal ditambahkan.');
        }

        return (int) $id;
    }

    public function ubah(int $id, array $input): void
    {
        $this->ambil($id);

        $data = $this->bersihkan($input);

        if (!$this->kalenderModel->update($id, $data)) {
            $this->gagalSimpan('Kegiatan kalender akademik gagal diperbarui (id ' . $id . ').');
        }
    }

    public function hapus(int $id): void
    {
        $this->ambil($id);

        if ($this->kalenderModel->delete($id) === false) {
            throw new RuntimeException('Kegiatan kalender akademik gagal dihapus (id ' . $id . ').');
        }
    }

    /**
     * Rapikan input lalu validasi. Semua pesan error dikumpulkan jadi satu.
     * tanggal_selesai kosong disimpan sebagai NULL, bukan string kosong.
     */
    private function bersihkan(array $input): array
    {
        helper('teks');

        $kegiatan       = rapikan_teks((string) ($input['kegiatan'] ?? ''));
        $tanggalMulai   = trim((string) ($input['tanggal_mulai'] ?? ''));
        $tanggalSelesai = trim((string) ($input['tanggal_selesai'] ?? ''));
        $semester       = (string) ($input['semester'] ?? '');
        $tahunAjaran    = rapikan_teks((string) ($input['tahun_ajaran'] ?? ''));
        $status         = (string) ($input['status'] ?? '');

        $errors      = [];
        $mulaiValid  = false;
        $selesaiValid = true;

        if ($kegiatan === '') {
            $errors[] = 'Kegiatan wajib diisi.';
        } elseif (mb_strlen($kegiatan) < 3) {
            $errors[] = 'Kegiatan minimal 3 karakter.';
        } elseif (mb_strlen($kegiatan) > 255) {
            $errors[] = 'Kegiatan maksimal 255 karakter.';
        }

        if ($tanggalMulai === '') {
            $errors[] = 'Tanggal mulai wajib diisi.';
        } elseif (strtotime($tanggalMulai) === false) {
            $errors[] = 'Tanggal mulai tidak valid.';
        } else {
            $mulaiValid = true;
        }

        if ($tanggalSelesai !== '' && strtotime($tanggalSelesai) === false) {
            $errors[]     = 'Tanggal selesai tidak valid.';
            $selesaiValid = false;
        }

        if ($mulaiValid && $selesaiValid && $tanggalSelesai !== ''
            && strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
            $errors[] = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
        }

        if (!in_array($semester, self::SEMESTER, true)) {
            $errors[] = 'Semester harus Ganjil atau Genap.';
        }

        if ($tahunAjaran === '') {
            $errors[] = 'Tahun ajaran wajib diisi.';
        }

        if (!in_array($status, self::STATUS, true)) {
            $errors[] = 'Status harus Published atau Draft.';
        }

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        return [
            'kegiatan'        => $kegiatan,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai !== '' ? $tanggalSelesai : null,
            'keterangan'      => $input['keterangan'] ?? null,
            'semester'        => $semester,
            'tahun_ajaran'    => $tahunAjaran,
            'status'          => $status,
        ];
    }

    /**
     * Kalau model menolak lewat validasinya sendiri (errors terisi), itu pesan aman.
     * Kalau errors kosong, berarti gagal di database.
     */
    private function gagalSimpan(string $pesanSistem): never
    {
        $errors = $this->kalenderModel->errors();

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        throw new RuntimeException($pesanSistem);
    }
}