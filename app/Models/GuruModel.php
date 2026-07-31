<?php

namespace App\Models;

use CodeIgniter\Model;

class GuruModel extends Model
{
    protected $table            = 'guru';
    protected $primaryKey       = 'id_guru';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'user_id', 'nip', 'nuptk', 'nama', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'jabatan', 'no_hp', 'email', 'foto', 'status',
    ];

    protected $validationRules = [
        'nip'           => 'required|is_unique[guru.nip,id_guru,{id_guru}]|max_length[30]',
        'nama'          => 'required|min_length[3]|max_length[100]',
        'jenis_kelamin' => 'required|in_list[L,P]',
        'email'         => 'permit_empty|valid_email|is_unique[guru.email,id_guru,{id_guru}]',
        'tanggal_lahir' => 'permit_empty|valid_date',
    ];

    protected $validationMessages = [
        'nip' => [
            'is_unique' => 'NIP ini sudah terdaftar.',
        ],
    ];

    public function getAllWithUser()
    {
        return $this->select('guru.*, users.username, users.status as status_akun')
                    ->join('users', 'users.id_user = guru.user_id')
                    ->orderBy('guru.nama', 'ASC');
    }

    public function findWithUser($id_guru)
    {
        return $this->select('guru.*, users.username')
                    ->join('users', 'users.id_user = guru.user_id')
                    ->where('id_guru', $id_guru)
                    ->first();
    }
}