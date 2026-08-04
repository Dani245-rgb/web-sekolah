<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table         = 'audit_log';
    protected $primaryKey    = 'id_log';
    protected $allowedFields = ['user_id', 'username', 'aksi', 'keterangan', 'ip_address'];
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function catat(?int $userId, string $username, string $aksi, string $keterangan = ''): void
    {
        $this->insert([
            'user_id'    => $userId,
            'username'   => $username,
            'aksi'       => $aksi,
            'keterangan' => $keterangan,
            'ip_address' => \Config\Services::request()->getIPAddress(),
        ]);
    }
}