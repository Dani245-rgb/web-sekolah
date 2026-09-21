<?php

namespace App\Models;

use CodeIgniter\Model;

class GaleriModel extends BaseModel
{
    protected $table            = 'galeri';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['judul', 'foto', 'kategori', 'deskripsi', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'judul'    => 'required|min_length[3]|max_length[255]',
        'kategori' => 'required|in_list[Kegiatan,Fasilitas,Prestasi,Lainnya]',
        'status'   => 'required|in_list[Published,Draft]',
    ];

    public function getPublished(?string $kategori = null, int $limit = 12, int $offset = 0)
    {
        $builder = $this->where('status', 'Published')
            ->orderBy('created_at', 'DESC');

        if ($kategori !== null) {
            $builder->where('kategori', $kategori);
        }

        return $builder->findAll($limit, $offset);
    }
}