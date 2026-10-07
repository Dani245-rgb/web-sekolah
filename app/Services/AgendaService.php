<?php

namespace App\Services;

use App\Models\AgendaModel;
use InvalidArgumentException;
use RuntimeException;

/**
 * Logika bisnis agenda (admin): validasi, rapikan teks, simpan, hapus.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * RuntimeException         = kegagalan sistem (controller yang log + tampilkan pesan umum).
 */
class AgendaService
{
    public const TIDAK_DITEMUKAN = 'Data tidak ditemukan.';

    private const STATUS = ['Published', 'Draft'];

    public function __construct(
        private AgendaModel $agendaModel,
    ) {
    }

    public function daftar(): array
    {
        return $this->agendaModel->daftarTerbaru();
    }

    public function ambil(int $id): array
    {
        $agenda = $this->agendaModel->find($id);

        if (!$agenda) {
            throw new InvalidArgumentException(self::TIDAK_DITEMUKAN);
        }

        return $agenda;
    }

    public function tambah(array $input): int
    {
        $data = $this->bersihkan($input);

        $id = $this->agendaModel->insert($data);

        if (!$id) {
            $this->gagalSimpan('Agenda gagal ditambahkan.');
        }

        return (int) $id;
    }

    public function ubah(int $id, array $input): void
    {
        $this->ambil($id);

        $data = $this->bersihkan($input);

        if (!$this->agendaModel->update($id, $data)) {
            $this->gagalSimpan('Agenda gagal diperbarui (id ' . $id . ').');
        }
    }

    public function hapus(int $id): void
    {
        $this->ambil($id);

        if ($this->agendaModel->delete($id) === false) {
            throw new RuntimeException('Agenda gagal dihapus (id ' . $id . ').');
        }
    }

    /**
     * Rapikan input lalu validasi. Semua pesan error dikumpulkan jadi satu.
     */
    private function bersihkan(array $input): array
    {
        helper('teks');

        $judul   = rapikan_teks((string) ($input['judul'] ?? ''));
        $tanggal = trim((string) ($input['tanggal'] ?? ''));
        $waktu   = rapikan_teks((string) ($input['waktu'] ?? ''));
        $lokasi  = rapikan_teks((string) ($input['lokasi'] ?? ''));
        $status  = (string) ($input['status'] ?? '');

        $errors = [];

        if ($judul === '') {
            $errors[] = 'Judul wajib diisi.';
        } elseif (mb_strlen($judul) < 3) {
            $errors[] = 'Judul minimal 3 karakter.';
        } elseif (mb_strlen($judul) > 255) {
            $errors[] = 'Judul maksimal 255 karakter.';
        }

        if ($tanggal === '') {
            $errors[] = 'Tanggal wajib diisi.';
        } elseif (strtotime($tanggal) === false) {
            $errors[] = 'Tanggal tidak valid.';
        }

        if (!in_array($status, self::STATUS, true)) {
            $errors[] = 'Status harus Published atau Draft.';
        }

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        return [
            'judul'   => $judul,
            'tanggal' => $tanggal,
            'waktu'   => $waktu,
            'lokasi'  => $lokasi,
            'status'  => $status,
        ];
    }

    /**
     * Kalau model menolak lewat validasinya sendiri (errors terisi), itu pesan aman.
     * Kalau errors kosong, berarti gagal di database.
     */
    private function gagalSimpan(string $pesanSistem): never
    {
        $errors = $this->agendaModel->errors();

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        throw new RuntimeException($pesanSistem);
    }
}