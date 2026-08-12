<?php

namespace App\Models;

use CodeIgniter\Model;

class AgendaModel extends Model
{
    protected $table            = 'agenda';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['judul', 'tanggal', 'waktu', 'lokasi', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'judul'   => 'required|min_length[3]|max_length[255]',
        'tanggal' => 'required|valid_date',
        'status'  => 'required|in_list[Published,Draft]',
    ];

    // Ambil agenda yang akan datang (tanggal >= hari ini), urut terdekat dulu
    public function getUpcoming(int $limit = 5)
    {
        return $this->where('status', 'Published')
            ->where('tanggal >=', date('Y-m-d'))
            ->orderBy('tanggal', 'ASC')
            ->findAll($limit);
    }
}