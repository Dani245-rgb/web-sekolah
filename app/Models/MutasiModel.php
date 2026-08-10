<?php

namespace App\Models;

use CodeIgniter\Model;

class MutasiModel extends Model
{
    protected $table         = 'mutasi';
    protected $primaryKey    = 'id_mutasi';
    protected $allowedFields = ['id_siswa', 'id_tahun_ajaran', 'jenis_mutasi', 'tanggal_mutasi', 'sekolah_tujuan', 'alasan'];
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    public function getAllWithSiswa()
    {
        return $this->select('mutasi.*, siswa.nis, siswa.nisn, siswa.nama, tahun_ajaran.tahun_ajaran')
            ->join('siswa', 'siswa.id_siswa = mutasi.id_siswa')
            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = mutasi.id_tahun_ajaran')
            ->orderBy('mutasi.tanggal_mutasi', 'DESC')
            ->findAll();
    }
}