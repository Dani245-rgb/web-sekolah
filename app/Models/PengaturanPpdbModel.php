<?php

namespace App\Models;

use CodeIgniter\Model;

class PengaturanPpdbModel extends BaseModel
{
    protected $table      = 'pengaturan_ppdb';
    protected $primaryKey = 'id';
    public const STATUS = ['buka', 'tutup'];

    protected $allowedFields = ['id', 'status', 'pesan_tutup', 'tahun_ajaran', 'updated_at'];

    // karena cuma 1 baris, bikin helper ambil settingnya langsung
    public function getSetting()
    {
        return $this->find(1) ?? [
            'status'       => 'tutup',
            'pesan_tutup'  => 'Pendaftaran belum dibuka.',
            'tahun_ajaran' => null,
        ];
    }

    /**
     * Simpan pengaturan. Kalau barisnya belum ada, dibuat dengan id 1.
     */
    public function simpan(array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($this->find(1)) {
            return (bool) $this->update(1, $data);
        }

        $data['id'] = 1;

        return (bool) $this->insert($data);
    }
}
