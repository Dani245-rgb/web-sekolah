<?php

namespace App\Models;

use CodeIgniter\Model;

class PklJurnalModel extends Model
{
    protected $table            = 'pkl_jurnal';
    protected $primaryKey       = 'id_jurnal';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'id_penempatan', 'tanggal', 'kegiatan', 'kendala',
        'status_validasi', 'catatan_pembimbing',
    ];

    protected $validationRules = [
        'id_penempatan' => 'required|is_natural_no_zero',
        'tanggal'       => 'required|valid_date',
        'kegiatan'      => 'required|min_length[5]',
    ];

    protected $validationMessages = [
        'id_penempatan' => [
            // Kombinasi id_penempatan+tanggal harus unik (1 jurnal per hari), dicek manual di controller
            // karena pesan error unique constraint DB kurang ramah kalau ditampilkan langsung ke user.
        ],
    ];

    public function getByPenempatan(int $idPenempatan)
    {
        return $this->where('id_penempatan', $idPenempatan)
            ->orderBy('tanggal', 'DESC')
            ->findAll();
    }

    public function sudahAdaJurnalHariItu(int $idPenempatan, string $tanggal, ?int $kecualiId = null): bool
    {
        $builder = $this->where('id_penempatan', $idPenempatan)->where('tanggal', $tanggal);
        if ($kecualiId) {
            $builder->where('id_jurnal !=', $kecualiId);
        }
        return $builder->countAllResults() > 0;
    }
}