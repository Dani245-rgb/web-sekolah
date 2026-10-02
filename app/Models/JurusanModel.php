<?php

namespace App\Models;

use CodeIgniter\Model;

class JurusanModel extends BaseModel
{
    protected $table         = 'jurusan';
    protected $primaryKey    = 'id_jurusan';
    protected $allowedFields = ['nama_jurusan', 'singkatan', 'slug', 'deskripsi', 'foto', 'kompetensi', 'prospek_kerja'];
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    public function getBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Semua jurusan, urut nama (untuk halaman jurusan publik).
     */
    public function getSemuaUrut(): array
    {
        return $this->orderBy('nama_jurusan', 'ASC')->findAll();
    }

    /**
     * Apakah nama sudah dipakai (termasuk yang soft-deleted).
     * $excludeId diisi saat update supaya tidak bentrok dengan dirinya sendiri.
     */
    public function namaSudahDipakai(string $nama, ?int $excludeId = null): bool
    {
        $builder = $this->withDeleted()->where('nama_jurusan', $nama);

        if ($excludeId !== null) {
            $builder->where('id_jurusan !=', $excludeId);
        }

        return $builder->first() !== null;
    }

    /**
     * Jurusan aktif untuk dropdown di form siswa.
     */
    public function getAktifUntukDropdown(): array
    {
        return $this->select('id_jurusan, kode_jurusan, nama_jurusan')
            ->where('deleted_at', null)
            ->where('status', 'Aktif')
            ->orderBy('nama_jurusan', 'ASC')
            ->findAll();
    }

    /**
     * Data jurusan untuk mencocokkan kode/singkatan/nama saat import siswa.
     */
    public function getUntukPencocokan(): array
    {
        return $this->select('id_jurusan, kode_jurusan, nama_jurusan, singkatan')
            ->where('deleted_at', null)
            ->findAll();
    }
}