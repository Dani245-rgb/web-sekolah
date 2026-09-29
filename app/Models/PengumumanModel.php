<?php

namespace App\Models;

use CodeIgniter\Model;

class PengumumanModel extends BaseModel
{
    protected $table            = 'pengumuman';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['judul', 'slug', 'isi', 'tanggal_publish', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'judul'           => 'required|min_length[3]|max_length[255]',
        'isi'             => 'required',
        'tanggal_publish' => 'required|valid_date',
        'status'          => 'required|in_list[Published,Draft]',
    ];

    public function getPublished(int $limit = 10, int $offset = 0)
    {
        return $this->where('status', 'Published')
            ->where('tanggal_publish <=', date('Y-m-d'))
            ->orderBy('tanggal_publish', 'DESC')
            ->paginate($limit, 'default', ($offset / $limit) + 1);
    }

    public function getBySlug(string $slug)
    {
        return $this->where('slug', $slug)
            ->where('status', 'Published')
            ->where('tanggal_publish <=', date('Y-m-d'))
            ->first();
    }

    public function getTerbaru(int $limit = 5): array
    {
        return $this->where('status', 'Published')
            ->where('tanggal_publish <=', date('Y-m-d'))
            ->orderBy('tanggal_publish', 'DESC')
            ->findAll($limit);
    }

    public function judulSudahAda(string $judul, ?int $excludeId = null): bool
    {
        $builder = $this->where('judul', $judul);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first() !== null;
    }
}
