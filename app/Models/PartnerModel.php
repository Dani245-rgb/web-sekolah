<?php

namespace App\Models;

use CodeIgniter\Model;

class PartnerModel extends BaseModel
{
    protected $table            = 'partner';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama', 'slug', 'foto', 'deskripsi', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama'      => 'required|min_length[3]|max_length[255]',
        'deskripsi' => 'required',
        'status'    => 'required|in_list[Published,Draft]',
    ];

    public function getPublished(int $limit = 10)
    {
        return $this->where('status', 'Published')
            ->orderBy('nama', 'ASC')
            ->findAll($limit);
    }

    public function getBySlug(string $slug)
    {
        return $this->where('slug', $slug)
            ->where('status', 'Published')
            ->first();
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