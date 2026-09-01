<?php

namespace App\Models;

use CodeIgniter\Model;

class TugasSubmisiModel extends Model
{
    protected $table            = 'tugas_submisi';
    protected $primaryKey       = 'id_submisi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'id_tugas', 'id_siswa', 'isi_jawaban', 'file_jawaban',
        'waktu_kumpul', 'status', 'nilai', 'catatan_guru',
    ];

    public function getByTugas(int $idTugas)
    {
        return $this->select('tugas_submisi.*, siswa.nama as nama_siswa, siswa.nis')
            ->join('siswa', 'siswa.id_siswa = tugas_submisi.id_siswa')
            ->where('id_tugas', $idTugas)
            ->orderBy('siswa.nama', 'ASC')
            ->findAll();
    }

    public function getSiswaBelumSubmit(int $idTugas, array $idSiswaSekelas): array
    {
        $sudahSubmit = array_column($this->where('id_tugas', $idTugas)->findAll(), 'id_siswa');
        return array_diff($idSiswaSekelas, $sudahSubmit);
    }

    public function getBySiswaTugas(int $idTugas, int $idSiswa)
    {
        return $this->where('id_tugas', $idTugas)->where('id_siswa', $idSiswa)->first();
    }
}