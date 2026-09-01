<?php

namespace App\Models;

use CodeIgniter\Model;

class MateriModel extends Model
{
    protected $table            = 'materi';
    protected $primaryKey       = 'id_materi';
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'id_jadwal', 'id_guru', 'judul', 'deskripsi', 'file_materi',
    ];

    public function getByGuru($idGuru)
    {
        return $this->select('materi.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('jadwal', 'jadwal.id_jadwal = materi.id_jadwal')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->where('materi.id_guru', $idGuru)
            ->orderBy('materi.created_at', 'DESC')
            ->findAll();
    }

    public function getByKelas($idKelas)
    {
        return $this->select('materi.*, mapel.nama_mapel, guru.nama as nama_guru')
            ->join('jadwal', 'jadwal.id_jadwal = materi.id_jadwal')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->join('guru', 'guru.id_guru = materi.id_guru')
            ->where('jadwal.id_kelas', $idKelas)
            ->orderBy('materi.created_at', 'DESC')
            ->findAll();
    }
}