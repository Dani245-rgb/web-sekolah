<?php

namespace App\Models;

use CodeIgniter\Model;

class MapelModel extends Model
{
    protected $table            = 'mapel';
    protected $primaryKey       = 'id_mapel';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'kode_mapel', 'nama_mapel', 'kelompok_mapel', 'kkm', 'semester', 'status',
    ];

    protected $validationRules = [
        'kode_mapel' => 'required|max_length[20]|is_unique[mapel.kode_mapel,id_mapel,{id_mapel}]',
        'nama_mapel' => 'required|min_length[3]|max_length[100]',
        'kkm'        => 'permit_empty|is_natural',
        'semester'   => 'permit_empty|in_list[Ganjil,Genap]',
        'status'     => 'required|in_list[Aktif,Nonaktif]',
    ];

    protected $validationMessages = [
        'kode_mapel' => [
            'is_unique' => 'Kode Mapel ini sudah terdaftar.',
        ],
    ];

    public function getForDropdown()
    {
        return $this->where('status', 'Aktif')->orderBy('nama_mapel', 'ASC')->findAll();
    }

    // Slot untuk cek pemakaian di Jadwal — sementara selalu 0, diganti COUNT beneran begitu modul Jadwal selesai
    public function isDipakaiDiJadwal($id_mapel): bool
    {
        return false;
    }
}