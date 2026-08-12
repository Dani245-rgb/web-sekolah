<?php

namespace App\Models;

use CodeIgniter\Model;

class PartnerModel extends Model
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

    public function generateUniqueSlug(string $nama, ?int $excludeId = null): string
    {
        $baseSlug = url_title($nama, '-', true);
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