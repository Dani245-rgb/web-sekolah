<?php

namespace App\Models;

use CodeIgniter\Model;

class TugasModel extends Model
{
    protected $table            = 'tugas';
    protected $primaryKey       = 'id_tugas';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'id_jadwal', 'id_guru', 'id_komponen', 'judul', 'deskripsi',
        'file_lampiran', 'tanggal_mulai', 'tenggat', 'status',
    ];

    protected $validationRules = [
        'judul'         => 'required|min_length[3]|max_length[150]',
        'tanggal_mulai' => 'required|valid_date',
        'tenggat'       => 'required|valid_date',
        'status'        => 'required|in_list[Aktif,Ditutup]',
    ];

    public function getByJadwal(int $idJadwal)
    {
        return $this->where('id_jadwal', $idJadwal)
            ->orderBy('tenggat', 'DESC')
            ->findAll();
    }

    public function getByGuru(int $idGuru)
    {
        return $this->select('tugas.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('jadwal', 'jadwal.id_jadwal = tugas.id_jadwal')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->where('tugas.id_guru', $idGuru)
            ->orderBy('tugas.tenggat', 'DESC')
            ->findAll();
    }

    public function getByKelasUntukSiswa(int $idKelas)
    {
        return $this->select('tugas.*, mapel.nama_mapel, guru.nama as nama_guru')
            ->join('jadwal', 'jadwal.id_jadwal = tugas.id_jadwal')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->join('guru', 'guru.id_guru = tugas.id_guru')
            ->where('jadwal.id_kelas', $idKelas)
            ->where('tugas.status', 'Aktif')
            ->orderBy('tugas.tenggat', 'ASC')
            ->findAll();
    }
}