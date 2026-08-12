<?php

namespace App\Models;

use CodeIgniter\Model;

class EkstrakurikulerModel extends Model
{
    protected $table            = 'ekstrakurikuler';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama', 'foto', 'deskripsi', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama'   => 'required|min_length[3]|max_length[100]',
        'status' => 'required|in_list[Published,Draft]',
    ];

    public function getPublished(int $limit = 8)
    {
        return $this->where('status', 'Published')
            ->orderBy('nama', 'ASC')
            ->findAll($limit);
    }
}