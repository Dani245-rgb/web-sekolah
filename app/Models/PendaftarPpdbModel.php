<?php

namespace App\Models;

use CodeIgniter\Model;

class PendaftarPpdbModel extends BaseModel
{
    protected $table            = 'pendaftar_ppdb';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
        'asal_sekolah', 'jurusan_pilihan', 'no_hp', 'email', 'alamat', 'status',
        'tahun_ajaran',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama_lengkap'    => 'required|min_length[3]|max_length[255]',
        'jenis_kelamin'   => 'required|in_list[Laki-laki,Perempuan]',
        'jurusan_pilihan' => 'required',
        'no_hp'           => 'required|min_length[9]|max_length[20]|is_unique[pendaftar_ppdb.no_hp]',
        'email'           => 'permit_empty|valid_email',
    ];

    protected $validationMessages = [
        'no_hp' => [
            'is_unique' => 'Nomor HP ini sudah pernah digunakan untuk mendaftar. Jika ini bukan Anda yang mendaftar, silakan hubungi pihak sekolah.',
        ],
    ];
}