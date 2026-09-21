<?php

namespace App\Models;

use CodeIgniter\Model;

class AnggotaOrganisasiModel extends BaseModel
{
    protected $table            = 'anggota_organisasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id_organisasi', 'nama', 'jabatan', 'foto', 'urutan'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama'    => 'required|min_length[2]|max_length[255]',
        'jabatan' => 'required|max_length[100]',
    ];
}