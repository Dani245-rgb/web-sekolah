<?php

namespace App\Models;

use CodeIgniter\Model;

class UnduhanModel extends Model
{
    protected $table         = 'unduhan';
    protected $primaryKey    = 'id_unduhan';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'kategori', 'judul', 'deskripsi', 'file', 'nama_file_asli',
        'ukuran_file', 'ekstensi', 'status',
    ];

    public function getPublished(?string $kategori = null)
    {
        $builder = $this->where('status', 'Published');

        if ($kategori) {
            $builder->where('kategori', $kategori);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    public function getKategoriList(): array
    {
        return $this->distinct()
                    ->select('kategori')
                    ->orderBy('kategori', 'ASC')
                    ->findAll();
    }

    public function tambahJumlahUnduh(int $id): void
    {
        $this->set('jumlah_unduh', 'jumlah_unduh + 1', false)->update($id);
    }
}