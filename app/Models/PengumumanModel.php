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
            ->orderBy('tanggal_publish', 'DESC')
            ->paginate($limit, 'default', ($offset / $limit) + 1);
    }
    
    public function getBySlug(string $slug)
    {
        return $this->where('slug', $slug)
            ->where('status', 'Published')
            ->first();
    }

    public function generateUniqueSlug(string $judul, ?int $excludeId = null): string
    {
        $baseSlug = url_title($judul, '-', true);
        $slug     = $baseSlug;
        $i        = 1;

        while (true) {
            $builder = $this->where('slug', $slug);
            if ($excludeId !== null) {
                $builder->where('id !=', $excludeId);
            }
            if (!$builder->first()) {
                break;
            }
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
