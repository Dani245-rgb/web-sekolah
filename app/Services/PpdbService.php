<?php

namespace App\Services;

use App\Models\NotifikasiModel;
use App\Models\PendaftarPpdbModel;
use App\Models\PengaturanPpdbModel;

/**
 * Urusan pendaftaran PPDB dari sisi publik: cek dibuka/tutup, aturan, simpan, kabari admin.
 */
class PpdbService
{
    private ?array $setting = null;

    public function __construct(
        protected PendaftarPpdbModel $pendaftarModel,
        protected PengaturanPpdbModel $settingModel,
        protected NotifikasiModel $notifikasiModel,
    ) {
    }

    public function setting(): array
    {
        return $this->setting ??= $this->settingModel->getSetting();
    }

    public function dibuka(): bool
    {
        return ($this->setting()['status'] ?? 'tutup') === 'buka';
    }

    /**
     * Aturan di luar validasi biasa. Return pesan error, atau null kalau lolos.
     */
    public function cekAturan(array $data): ?string
    {
        $lahir = strtotime((string) ($data['tanggal_lahir'] ?? ''));

        if ($lahir !== false && $lahir > time()) {
            return 'Tanggal lahir tidak boleh di masa depan.';
        }

        return null;
    }

    /**
     * Simpan pendaftar (tahun ajaran diambil dari pengaturan) dan buat notifikasi untuk admin.
     * Return id pendaftar, atau false kalau gagal disimpan (lihat errors()).
     */
    public function daftar(array $data): int|false
    {
        $data['tahun_ajaran'] = $this->setting()['tahun_ajaran'] ?? null;

        $id = $this->pendaftarModel->insert($data);

        if (!$id) {
            return false;
        }

        $this->notifikasiModel->buat(
            null,
            'Pendaftar PPDB Baru',
            'ppdb_baru',
            'Ada pendaftar baru: ' . $data['nama_lengkap'],
            '/admin/ppdb/detail/' . $id
        );

        return (int) $id;
    }

    public function errors(): array
    {
        return $this->pendaftarModel->errors();
    }
}