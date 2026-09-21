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
}
