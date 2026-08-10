<?php

namespace App\Models;

use CodeIgniter\Model;

class RuanganModel extends Model
{
    protected $table         = 'ruangan';
    protected $primaryKey    = 'id_ruangan';
    protected $allowedFields = ['nama_ruangan', 'kapasitas', 'status'];
    protected $returnType    = 'array';
    protected $useTimestamps = true;
}