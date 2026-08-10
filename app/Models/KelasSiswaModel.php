<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasSiswaModel extends Model
{
    protected $table         = 'kelas_siswa';
    protected $primaryKey    = 'id_kelas_siswa';
    protected $allowedFields = ['id_kelas', 'id_siswa', 'id_tahun_ajaran'];
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    /**
     * Siswa Aktif yang BELUM punya baris kelas_siswa di tahun ajaran tertentu.
     */
    public function getSiswaBelumAssign($idTahunAjaran)
    {
        return $this->db->table('siswa')
            ->select('siswa.id_siswa, siswa.nis, siswa.nama')
            ->where('siswa.status', 'Aktif')
            ->whereNotIn('siswa.id_siswa', function ($builder) use ($idTahunAjaran) {
                return $builder->select('id_siswa')
                    ->from('kelas_siswa')
                    ->where('id_tahun_ajaran', $idTahunAjaran);
            })
            ->orderBy('siswa.nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getAllWithDetail($idTahunAjaran = null)
    {
        $builder = $this->select('kelas_siswa.*, siswa.nis, siswa.nama, kelas.nama_kelas, tahun_ajaran.tahun_ajaran')
            ->join('siswa', 'siswa.id_siswa = kelas_siswa.id_siswa')
            ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas')
            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = kelas_siswa.id_tahun_ajaran');

        if ($idTahunAjaran) {
            $builder->where('kelas_siswa.id_tahun_ajaran', $idTahunAjaran);
        }

        return $builder->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama', 'ASC')
            ->findAll();
    }

    public function getSiswaByKelas($idKelas, $idTahunAjaran)
    {
        return $this->select('kelas_siswa.id_siswa, siswa.nis, siswa.nama')
            ->join('siswa', 'siswa.id_siswa = kelas_siswa.id_siswa')
            ->where('kelas_siswa.id_kelas', $idKelas)
            ->where('kelas_siswa.id_tahun_ajaran', $idTahunAjaran)
            ->where('siswa.status', 'Aktif')
            ->orderBy('siswa.nama', 'ASC')
            ->findAll();
    }

    public function getRiwayatBySiswa($idSiswa)
    {
        return $this->select('kelas_siswa.*, kelas.nama_kelas, tahun_ajaran.tahun_ajaran')
            ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas')
            ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = kelas_siswa.id_tahun_ajaran')
            ->where('kelas_siswa.id_siswa', $idSiswa)
            ->orderBy('tahun_ajaran.tahun_ajaran', 'ASC')
            ->findAll();
    }
}
