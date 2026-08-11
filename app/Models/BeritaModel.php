<?php

namespace App\Models;

use CodeIgniter\Model;

class BeritaModel extends Model
{
    protected $table            = 'berita';
    protected $primaryKey       = 'id_berita';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'judul',
        'slug',
        'kategori',
        'gambar',
        'ringkasan',
        'konten',
        'status',
        'posisi',
        'tanggal_publish',
        'id_user',
    ];

    protected $validationRules = [
        'judul'    => 'required|min_length[5]|max_length[200]',
        'kategori' => 'required|in_list[Akademik,Prestasi,Kegiatan,Umum]',
        'konten'   => 'required',
        'status'   => 'required|in_list[Draft,Published]',
        'posisi'   => 'required|in_list[hero,utama,biasa,populer,hits]',
    ];

    public function getPublished(int $limit = 0, int $offset = 0)
    {
        $builder = $this->where('status', 'Published')
            ->orderBy('tanggal_publish', 'DESC')
            ->orderBy('id_berita', 'DESC');

        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        return $builder->findAll();
    }

    public function getHero()
    {
        return $this->where('status', 'Published')
            ->where('posisi', 'hero')
            ->orderBy('tanggal_publish', 'DESC')
            ->first();
    }

    public function getUtama(int $limit = 3)
    {
        return $this->where('status', 'Published')
            ->where('posisi', 'utama')
            ->orderBy('tanggal_publish', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getTerbaru(int $limit = 4, int $offset = 0)
    {
        return $this->where('status', 'Published')
            ->where('posisi', 'biasa')
            ->orderBy('tanggal_publish', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    public function getBySlug(string $slug)
    {
        return $this->where('slug', $slug)->where('status', 'Published')->first();
    }

    public function getPopuler(int $limit = 5)
    {
        return $this->where('status', 'Published')
            ->where('posisi', 'populer')
            ->orderBy('tanggal_publish', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getHits(int $limit = 5)
    {
        return $this->where('status', 'Published')
            ->where('posisi', 'hits')
            ->orderBy('tanggal_publish', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function generateUniqueSlug(string $judul, ?int $excludeId = null): string
    {
        $baseSlug = url_title($judul, '-', true);
        $slug = $baseSlug;
        $i = 1;

        while (true) {
            $query = $this->where('slug', $slug);
            if ($excludeId) {
                $query->where('id_berita !=', $excludeId);
            }
            if (!$query->first()) {
                break;
            }
            $slug = $baseSlug . '-' . $i++;
        }

        return $slug;
    }
}
