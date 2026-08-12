<?php

namespace App\Models;

use CodeIgniter\Model;

class PendaftarPpdbModel extends Model
{
    protected $table            = 'pendaftar_ppdb';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
        'asal_sekolah', 'jurusan_pilihan', 'no_hp', 'email', 'alamat', 'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama_lengkap'    => 'required|min_length[3]|max_length[255]',
        'jenis_kelamin'   => 'required|in_list[Laki-laki,Perempuan]',
        'jurusan_pilihan' => 'required',
        'no_hp'           => 'required|min_length[9]|max_length[20]',
        'email'           => 'permit_empty|valid_email',
    ];
}