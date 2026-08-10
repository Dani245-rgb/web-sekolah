<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanNilaiModel extends Model
{
    protected $table = 'pengaturan_nilai';
    protected $primaryKey = 'id_pengaturan';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['id_guru', 'id_kelas', 'id_mapel', 'id_semester', 'kkm', 'created_at'];

    public function getPengaturan(int $id_guru, int $id_kelas, int $id_mapel, int $id_semester)
    {
        return $this->where([
            'id_guru'     => $id_guru,
            'id_kelas'    => $id_kelas,
            'id_mapel'    => $id_mapel,
            'id_semester' => $id_semester,
        ])->first();
    }
}