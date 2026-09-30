<?php

namespace App\Services;

use App\Models\NotifikasiModel;
use App\Models\PesanKontakModel;

/**
 * Urusan pesan dari form Kontak publik: simpan, lalu kabari admin lewat notifikasi.
 */
class KontakService
{
    public function __construct(
        protected PesanKontakModel $pesanModel,
        protected NotifikasiModel $notifikasiModel,
    ) {
    }

    /**
     * Simpan pesan dan buat notifikasi broadcast untuk admin.
     * Return id pesan, atau false kalau gagal disimpan (lihat errors()).
     */
    public function kirim(string $nama, string $email, string $subjek, string $pesan): int|false
    {
        $id = $this->pesanModel->insert([
            'nama'   => $nama,
            'email'  => $email,
            'subjek' => $subjek,
            'pesan'  => $pesan,
        ]);

        if (!$id) {
            return false;
        }

        $ringkasan = 'Pesan dari ' . $nama . ': ' . ($subjek !== '' ? $subjek : '(tanpa subjek)');

        $this->notifikasiModel->buat(
            null,
            'Pesan Masuk Baru',
            'pesan_masuk',
            mb_strimwidth($ringkasan, 0, 200, '...'),
            '/admin/kontak/detail/' . $id
        );

        return (int) $id;
    }

    public function errors(): array
    {
        return $this->pesanModel->errors();
    }
}