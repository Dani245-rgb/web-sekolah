<?php

namespace App\Models;

use CodeIgniter\Model;

class KomponenNilaiModel extends Model
{
    protected $table = 'komponen_nilai';
    protected $primaryKey = 'id_komponen';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['id_pengaturan', 'id_kategori', 'nama_komponen', 'link_referensi', 'bobot', 'urutan'];
}
