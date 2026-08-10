<?php

namespace App\Models;

use CodeIgniter\Model;

class SemesterModel extends Model
{
    protected $table         = 'semester';
    protected $primaryKey    = 'id_semester';
    protected $allowedFields = ['id_tahun_ajaran', 'nama_semester', 'tanggal_mulai', 'tanggal_selesai', 'status'];
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    public function getActive()
    {
        return $this->where('status', 'Aktif')->first();
    }

    public function setActive(int $id)
    {
        $this->where('status', 'Aktif')->set(['status' => 'Nonaktif'])->update();
        return $this->update($id, ['status' => 'Aktif']);
    }
}