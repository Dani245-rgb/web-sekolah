<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriNilaiModel extends Model
{
    protected $table            = 'kategori_nilai';
    protected $primaryKey       = 'id_kategori';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'id_pengaturan',
        'nama_kategori',
        'bobot',
        'created_at',
    ];

    protected $useTimestamps = false;

    public function getByPengaturan(int $idPengaturan): array
    {
        return $this->where('id_pengaturan', $idPengaturan)
                    ->orderBy('id_kategori', 'ASC')
                    ->findAll();
    }

    public function getTotalBobot(int $idPengaturan): float
    {
        $result = $this->selectSum('bobot')
                        ->where('id_pengaturan', $idPengaturan)
                        ->first();
        return (float) ($result['bobot'] ?? 0);
    }
}