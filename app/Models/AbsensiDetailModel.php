<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiDetailModel extends Model
{
    protected $table         = 'absensi_detail';
    protected $primaryKey    = 'id_absensi_detail';
    protected $allowedFields = ['id_absensi_jadwal', 'id_siswa', 'status', 'keterangan'];
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    public function getByKelasTanggal($idKelas, $tanggal)
    {
        return $this->select('absensi_detail.*, siswa.nis, siswa.nama, jadwal.jam_mulai, jadwal.jam_selesai, mapel.nama_mapel')
            ->join('absensi_jadwal', 'absensi_jadwal.id_absensi_jadwal = absensi_detail.id_absensi_jadwal')
            ->join('jadwal', 'jadwal.id_jadwal = absensi_jadwal.id_jadwal')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->join('siswa', 'siswa.id_siswa = absensi_detail.id_siswa')
            ->where('absensi_jadwal.tanggal', $tanggal)
            ->where('jadwal.id_kelas', $idKelas)
            ->orderBy('jadwal.jam_mulai', 'ASC')
            ->orderBy('siswa.nama', 'ASC')
            ->findAll();
    }

    public function getBySiswaRange($idSiswa, $tglMulai, $tglSelesai)
    {
        return $this->select('absensi_detail.*, absensi_jadwal.tanggal, mapel.nama_mapel')
            ->join('absensi_jadwal', 'absensi_jadwal.id_absensi_jadwal = absensi_detail.id_absensi_jadwal')
            ->join('jadwal', 'jadwal.id_jadwal = absensi_jadwal.id_jadwal')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->where('absensi_detail.id_siswa', $idSiswa)
            ->where('absensi_jadwal.tanggal >=', $tglMulai)
            ->where('absensi_jadwal.tanggal <=', $tglSelesai)
            ->orderBy('absensi_jadwal.tanggal', 'DESC')
            ->findAll();
    }

    public function rekapPerSiswa($idSiswa, $tglMulai, $tglSelesai)
    {
        $rows = $this->getBySiswaRange($idSiswa, $tglMulai, $tglSelesai);
        $rekap = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alfa' => 0];
        foreach ($rows as $r) {
            $rekap[$r['status']]++;
        }
        return $rekap;
    }

    public function getMatrixByJadwal($idJadwal, array $tanggalList)
    {
        if (empty($tanggalList)) return [];

        return $this->select('absensi_detail.id_siswa, absensi_detail.status, absensi_jadwal.tanggal')
            ->join('absensi_jadwal', 'absensi_jadwal.id_absensi_jadwal = absensi_detail.id_absensi_jadwal')
            ->where('absensi_jadwal.id_jadwal', $idJadwal)
            ->whereIn('absensi_jadwal.tanggal', $tanggalList)
            ->findAll();
    }
}
