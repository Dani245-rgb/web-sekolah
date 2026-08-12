<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfilKontenModel extends Model
{
    protected $table            = 'profil_konten';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['jenis', 'judul', 'konten', 'foto', 'nama', 'jabatan'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByJenis(string $jenis)
    {
        return $this->where('jenis', $jenis)->first();
    }

    // Ambil data, kalau belum ada baris untuk jenis ini, buat default kosong
    public function getOrCreate(string $jenis): array
    {
        $data = $this->getByJenis($jenis);

        if (!$data) {
            $this->insert(['jenis' => $jenis]);
            $data = $this->getByJenis($jenis);
        }

        return $data;
    }
}