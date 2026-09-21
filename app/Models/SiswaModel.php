<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends BaseModel
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id_siswa';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'user_id',
        'nis',
        'nisn',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ortu',
        'no_hp_ortu',
        'email',
        'foto',
    ];

    protected $validationRules = [
        'nis'           => 'required|max_length[20]|is_unique[siswa.nis,id_siswa,{id_siswa}]',
        'nisn'          => 'required|max_length[20]|is_unique[siswa.nisn,id_siswa,{id_siswa}]',
        'nama'          => 'required|min_length[3]|max_length[100]',
        'jenis_kelamin' => 'required|in_list[L,P]',
        'email'         => 'permit_empty|valid_email',
    ];

    protected $validationMessages = [
        'nis' => [
            'is_unique' => 'NIS ini sudah terdaftar.',
        ],
        'nisn' => [
            'is_unique' => 'NISN ini sudah terdaftar.',
        ],
    ];
    
    public function getAll()
    {
        return $this->orderBy('nama', 'ASC');
    }
}
