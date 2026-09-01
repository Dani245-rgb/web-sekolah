<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanPpdbModel extends Model
{
    protected $table      = 'pengaturan_ppdb';
    protected $primaryKey = 'id';
    protected $allowedFields = ['status', 'pesan_tutup', 'tahun_ajaran', 'updated_at'];

    // karena cuma 1 baris, bikin helper ambil settingnya langsung
    public function getSetting()
    {
        return $this->find(1) ?? [
            'status'       => 'tutup',
            'pesan_tutup'  => 'Pendaftaran belum dibuka.',
            'tahun_ajaran' => null,
        ];
    }
}