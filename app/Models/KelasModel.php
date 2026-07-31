<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasModel extends Model
{
    protected $table            = 'kelas';
    protected $primaryKey       = 'id_kelas';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'tingkat', 'jurusan', 'rombel', 'nama_kelas', 'wali_kelas_id',
        'ruangan', 'kapasitas', 'id_tahun_ajaran', 'status',
    ];

    protected $validationRules = [
        'tingkat'         => 'required|in_list[X,XI,XII]',
        'jurusan'         => 'required|max_length[50]',
        'rombel'          => 'required|is_natural_no_zero',
        'kapasitas'       => 'permit_empty|is_natural_no_zero',
        'id_tahun_ajaran' => 'required|is_natural_no_zero',
        'status'          => 'required|in_list[Aktif,Nonaktif]',
    ];

    // Join ke guru (wali kelas) & tahun_ajaran, sekaligus hitung jumlah siswa
    public function getAllWithRelasi()
    {
        return $this->select('kelas.*, guru.nama as nama_wali, tahun_ajaran.tahun_ajaran')
                    ->join('guru', 'guru.id_guru = kelas.wali_kelas_id', 'left')
                    ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = kelas.id_tahun_ajaran', 'left')
                    ->orderBy('kelas.tingkat', 'ASC')
                    ->orderBy('kelas.jurusan', 'ASC');
    }

    public function findWithRelasi($id_kelas)
    {
        return $this->select('kelas.*, guru.nama as nama_wali, tahun_ajaran.tahun_ajaran')
                    ->join('guru', 'guru.id_guru = kelas.wali_kelas_id', 'left')
                    ->join('tahun_ajaran', 'tahun_ajaran.id_tahun_ajaran = kelas.id_tahun_ajaran', 'left')
                    ->where('kelas.id_kelas', $id_kelas)
                    ->first();
    }

    // Hitung jumlah siswa di kelas ini — sementara return 0, nanti diganti COUNT(kelas_siswa) begitu modul Siswa selesai
    public function countSiswa($id_kelas): int
    {
        return 0;
    }

    // Cek apakah kombinasi tingkat+jurusan+rombel+tahun ajaran sudah ada (cegah duplikat nama_kelas di tahun yang sama)
    public function isDuplikat(string $tingkat, string $jurusan, int $rombel, int $idTahunAjaran, ?int $excludeId = null): bool
    {
        $builder = $this->where('tingkat', $tingkat)
                         ->where('jurusan', $jurusan)
                         ->where('rombel', $rombel)
                         ->where('id_tahun_ajaran', $idTahunAjaran);

        if ($excludeId) {
            $builder->where('id_kelas !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }
}