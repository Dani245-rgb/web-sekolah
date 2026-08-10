<?php

namespace App\Models;

use CodeIgniter\Model;

class AlumniModel extends Model
{
    protected $table         = 'alumni';
    protected $primaryKey    = 'id_alumni';
    protected $allowedFields = ['id_siswa', 'id_tahun_ajaran', 'tanggal_lulus', 'no_ijazah', 'keterangan'];
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    public function getAllWithSiswa()
    {
        return $this->select('alumni.*, siswa.nis, siswa.nisn, siswa.nama, tahun_ajaran.tahun_ajaran')
            ->join('siswa', 'siswa.id_siswa = alumni.id_siswa')
            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = alumni.id_tahun_ajaran')
            ->orderBy('alumni.tanggal_lulus', 'DESC')
            ->findAll();
    }
}