<?php

namespace App\Models;

use CodeIgniter\Model;

class JurusanModel extends Model
{
    protected $table         = 'jurusan';
    protected $primaryKey    = 'id_jurusan';
    protected $allowedFields = ['nama_jurusan'];
    protected $returnType    = 'array';
    protected $useTimestamps = false;
}