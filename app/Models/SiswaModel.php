<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id_siswa';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'nis', 'nisn', 'nama', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'agama', 'alamat', 'nama_ayah', 'nama_ibu',
        'pekerjaan_ortu', 'no_hp_ortu', 'email', 'foto', 'status',
    ];

    protected $validationRules = [
        'nis'           => 'required|max_length[20]|is_unique[siswa.nis,id_siswa,{id_siswa}]',
        'nisn'          => 'required|max_length[20]|is_unique[siswa.nisn,id_siswa,{id_siswa}]',
        'nama'          => 'required|min_length[3]|max_length[100]',
        'jenis_kelamin' => 'required|in_list[L,P]',
        'email'         => 'permit_empty|valid_email',
    ];

    protected $validationMessages = [
        'nis' => [
            'is_unique' => 'NIS ini sudah terdaftar.',
        ],
        'nisn' => [
            'is_unique' => 'NISN ini sudah terdaftar.',
        ],
    ];

    // Join ke kelas saat ini (berdasarkan tahun ajaran yang sedang Aktif)
    public function getAllWithKelas(int $idTahunAktif = 0)
    {
        return $this->select('siswa.*, kelas.nama_kelas')
                    ->join('kelas_siswa', "kelas_siswa.id_siswa = siswa.id_siswa AND kelas_siswa.id_tahun_ajaran = {$idTahunAktif}", 'left')
                    ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas', 'left')
                    ->orderBy('siswa.nama', 'ASC');
    }

    // Cek apakah siswa punya relasi ke modul lain (Nilai, Absensi, dll)
    // Slot dulu — selalu false sampai modul akademik dibuat
    public function isDipakaiDiModulLain($id_siswa): bool
    {
        return false;
    }
}