<?php

namespace App\Models;

use CodeIgniter\Model;

class NilaiSiswaModel extends Model
{
    protected $table = 'nilai_siswa';
    protected $primaryKey = 'id_nilai';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $allowedFields = ['id_komponen', 'id_siswa', 'nilai'];
}