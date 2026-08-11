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

    public function catat(?int $userId, ?string $username, string $aksi, string $keterangan = ''): void
    {
        $this->insert([
            'user_id'    => $userId,
            'username'   => $username ?? '-',
            'aksi'       => $aksi,
            'keterangan' => $keterangan,
            'ip_address' => \Config\Services::request()->getIPAddress(),
        ]);
    }

    /**
     * Query builder dengan filter, dipakai bareng oleh index() dan export (PDF/Excel)
     * biar hasil export selalu konsisten dengan apa yang lagi ditampilkan/difilter di layar.
     */
    public function filterQuery(array $filter)
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if (!empty($filter['aksi'])) {
            $builder = $builder->where('aksi', $filter['aksi']);
        }
        if (!empty($filter['username'])) {
            $builder = $builder->like('username', $filter['username']);
        }
        if (!empty($filter['dari'])) {
            $builder = $builder->where('created_at >=', $filter['dari'] . ' 00:00:00');
        }
        if (!empty($filter['sampai'])) {
            $builder = $builder->where('created_at <=', $filter['sampai'] . ' 23:59:59');
        }

        return $builder;
    }

    /**
     * Daftar aksi unik yang pernah tercatat, buat dropdown filter.
     */
    public function daftarAksi(): array
    {
        return $this->distinct()->select('aksi')->orderBy('aksi', 'ASC')->findColumn('aksi') ?? [];
    }
}
