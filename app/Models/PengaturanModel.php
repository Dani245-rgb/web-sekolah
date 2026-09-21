<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanModel extends BaseModel
{
    protected $table         = 'pengaturan_sekolah';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'nama_sekolah',
        'alamat',
        'telepon',
        'email',
        'logo',
        'favicon',
        'tahun_ajaran_aktif_id',
        'updated_at',
    ];
    protected $returnType = 'array';

    public function getPengaturan()
    {
        return $this->first();
    }
}