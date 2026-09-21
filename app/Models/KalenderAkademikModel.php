<?php

namespace App\Models;

use CodeIgniter\Model;

class KalenderAkademikModel extends BaseModel
{
    protected $table            = 'kalender_akademik';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'kegiatan', 'tanggal_mulai', 'tanggal_selesai', 'keterangan',
        'semester', 'tahun_ajaran', 'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'kegiatan'      => 'required|min_length[3]|max_length[255]',
        'tanggal_mulai' => 'required|valid_date',
        'semester'      => 'required|in_list[Ganjil,Genap]',
        'tahun_ajaran'  => 'required',
        'status'        => 'required|in_list[Published,Draft]',
    ];

    public function getPublished()
    {
        return $this->where('status', 'Published')
            ->orderBy('tanggal_mulai', 'ASC')
            ->findAll();
    }
}