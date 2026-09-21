<?php

namespace App\Models;

use CodeIgniter\Model;

class BkArtikelModel extends BaseModel
{
    protected $table         = 'bk_artikel';
    protected $primaryKey    = 'id_artikel';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'kategori', 'judul', 'slug', 'konten', 'foto',
        'link_eksternal', 'deadline', 'status', 'id_guru_penulis',
    ];

    public function getBySlug(string $slug)
    {
        return $this->where('slug', $slug)
                    ->where('status', 'Published')
                    ->first();
    }

    public function getPublishedByKategori(string $kategori)
    {
        return $this->where('kategori', $kategori)
                    ->where('status', 'Published')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}