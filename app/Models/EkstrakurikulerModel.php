<?php

namespace App\Models;

use CodeIgniter\Model;

class EkstrakurikulerModel extends BaseModel
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

        public function namaSudahAda(string $nama, ?int $excludeId = null): bool
    {
        $builder = $this->where('nama', $nama);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first() !== null;
    }
}