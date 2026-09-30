<?php

namespace App\Models;

use CodeIgniter\Model;

class PklPenempatanModel extends BaseModel
{
    protected $table            = 'pkl_penempatan';
    protected $primaryKey       = 'id_penempatan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'id_siswa',
        'id_guru_pembimbing',
        'nama_perusahaan',
        'alamat_perusahaan',
        'nama_pembimbing_industri',
        'no_hp_pembimbing_industri',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    public const STATUS = ['Aktif', 'Selesai', 'Dibatalkan'];

    protected $validationRules = [
        'id_siswa'           => 'required|is_natural_no_zero|is_not_unique[siswa.id_siswa]',
        'id_guru_pembimbing' => 'permit_empty|is_natural_no_zero|is_not_unique[guru.id_guru]',
        'nama_perusahaan'  => 'required|min_length[3]|max_length[150]',
        'tanggal_mulai'    => 'required|valid_date',
        'tanggal_selesai'  => 'permit_empty|valid_date',
        'status'           => 'required|in_list[Aktif,Selesai,Dibatalkan]',
    ];

    public function getBySiswa(int $idSiswa)
    {
        return $this->select('pkl_penempatan.*, guru.nama as nama_guru_pembimbing')
            ->join('guru', 'guru.id_guru = pkl_penempatan.id_guru_pembimbing', 'left')
            ->where('pkl_penempatan.id_siswa', $idSiswa)
            ->orderBy('pkl_penempatan.tanggal_mulai', 'DESC')
            ->findAll();
    }

    public function getAktifBySiswa(int $idSiswa)
    {
        return $this->select('pkl_penempatan.*, guru.nama as nama_guru_pembimbing')
            ->join('guru', 'guru.id_guru = pkl_penempatan.id_guru_pembimbing', 'left')
            ->where('pkl_penempatan.id_siswa', $idSiswa)
            ->where('pkl_penempatan.status', 'Aktif')
            ->orderBy('pkl_penempatan.tanggal_mulai', 'DESC')
            ->first();
    }

    public function getAllWithRelasi(array $filter = [])
    {
        $builder = $this->select('pkl_penempatan.*, siswa.nama as nama_siswa, siswa.nis, guru.nama as nama_guru_pembimbing')
            ->join('siswa', 'siswa.id_siswa = pkl_penempatan.id_siswa')
            ->join('guru', 'guru.id_guru = pkl_penempatan.id_guru_pembimbing', 'left');

        if (!empty($filter['id_guru_pembimbing'])) {
            $builder->where('pkl_penempatan.id_guru_pembimbing', $filter['id_guru_pembimbing']);
        }
        if (!empty($filter['status'])) {
            $builder->where('pkl_penempatan.status', $filter['status']);
        }
        if (!empty($filter['keyword'])) {
            $builder->groupStart()
                ->like('siswa.nama', $filter['keyword'])
                ->orLike('pkl_penempatan.nama_perusahaan', $filter['keyword'])
                ->groupEnd();
        }

        return $builder->orderBy('pkl_penempatan.tanggal_mulai', 'DESC')->findAll();
    }

    /**
     * Aturan bisnis di luar validasi biasa. Return pesan error, atau null kalau lolos.
     */
    public function cekAturan(array $data, ?int $kecualiId = null): ?string
    {
        if (
            !empty($data['tanggal_selesai'])
            && strtotime($data['tanggal_selesai']) < strtotime($data['tanggal_mulai'])
        ) {
            return 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
        }

        if (($data['status'] ?? null) === 'Aktif'
            && $this->sudahAdaAktif((int) $data['id_siswa'], $kecualiId)
        ) {
            return 'Siswa ini sudah punya penempatan PKL yang berstatus Aktif.';
        }

        return null;
    }

    public function sudahAdaAktif(int $idSiswa, ?int $kecualiId = null): bool
    {
        $builder = $this->where('id_siswa', $idSiswa)->where('status', 'Aktif');

        if ($kecualiId !== null) {
            $builder->where('id_penempatan !=', $kecualiId);
        }

        return $builder->countAllResults() > 0;
    }   
}
