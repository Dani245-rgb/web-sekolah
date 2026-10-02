<?php

namespace App\Models;

use CodeIgniter\Model;

class BkArtikelModel extends BaseModel
{
    protected $table         = 'bk_artikel';
    protected $primaryKey    = 'id_artikel';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    public const KATEGORI = [
        'kesehatan_mental' => 'Kesehatan Mental',
        'karier'           => 'Karier & Studi Lanjut',
        'tes_minat'        => 'Tes Minat & Bakat',
    ];

    protected $allowedFields = [
        'kategori', 'judul', 'slug', 'konten', 'foto',
        'link_eksternal', 'deadline', 'status', 'id_guru_penulis',
    ];

    public function getBySlug(string $slug)
    {
        return $this->where('slug', $slug)
                    ->where('status', 'Published')
                    ->first();
    }

    public function getPublishedByKategori(string $kategori)
    {
        return $this->where('kategori', $kategori)
                    ->where('status', 'Published')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Daftar untuk halaman admin (semua status), terbaru dulu. Kategori opsional.
     */
    public function getDaftarAdmin(?string $kategori = null): array
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if ($kategori !== null) {
            $builder->where('kategori', $kategori);
        }

        return $builder->findAll();
    }

    /**
     * Apakah judul sudah dipakai artikel lain.
     * $excludeId diisi saat update supaya tidak bentrok dengan dirinya sendiri.
     */
    public function judulSudahDipakai(string $judul, ?int $excludeId = null): bool
    {
        $builder = $this->where('judul', $judul);

        if ($excludeId !== null) {
            $builder->where('id_artikel !=', $excludeId);
        }

        return $builder->first() !== null;
    }
}