<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiJadwalModel extends Model
{
    protected $table         = 'absensi_jadwal';
    protected $primaryKey    = 'id_absensi_jadwal';
    protected $allowedFields = ['id_jadwal', 'tanggal', 'id_guru', 'created_at'];
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    public function cariByJadwalTanggal($idJadwal, $tanggal)
    {
        return $this->where('id_jadwal', $idJadwal)
            ->where('tanggal', $tanggal)
            ->first();
    }
}