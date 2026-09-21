<?php

namespace App\Models;

use CodeIgniter\Model;

class PrestasiModel extends BaseModel
{
    protected $table            = 'prestasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['judul', 'tim', 'tingkat', 'foto', 'tanggal', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'judul'   => 'required|min_length[3]|max_length[255]',
        'tingkat' => 'required|in_list[Kabupaten,Provinsi,Nasional,Internasional]',
        'tanggal' => 'required|valid_date',
        'status'  => 'required|in_list[Published,Draft]',
    ];

    public function getPublished(int $limit = 6, int $offset = 0)
    {
        return $this->where('status', 'Published')
            ->orderBy('tanggal', 'DESC')
            ->findAll($limit, $offset);
    }
}