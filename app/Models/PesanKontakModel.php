<?php

namespace App\Models;

use CodeIgniter\Model;

class PesanKontakModel extends Model
{
    protected $table            = 'pesan_kontak';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama', 'email', 'subjek', 'pesan', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama'  => 'required|min_length[3]|max_length[255]',
        'email' => 'required|valid_email',
        'pesan' => 'required',
    ];
}