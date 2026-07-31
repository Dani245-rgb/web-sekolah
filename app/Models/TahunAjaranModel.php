<?php

namespace App\Models;

use CodeIgniter\Model;

class TahunAjaranModel extends Model
{
    protected $table            = 'tahun_ajaran';
    protected $primaryKey       = 'id_tahun_ajaran';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'tahun_ajaran', 'semester', 'tanggal_mulai', 'tanggal_selesai', 'status',
    ];

    protected $validationRules = [
        'tahun_ajaran'    => 'required|max_length[20]',
        'semester'        => 'permit_empty|in_list[Ganjil,Genap]',
        'tanggal_mulai'   => 'permit_empty|valid_date',
        'tanggal_selesai' => 'permit_empty|valid_date',
        'status'          => 'required|in_list[Aktif,Tidak Aktif,Belum Aktif]',
    ];

    public function getForDropdown()
    {
        return $this->orderBy('tahun_ajaran', 'DESC')->findAll();
    }

    public function getActive()
    {
        return $this->where('status', 'Aktif')->first();
    }

    /**
     * Cuma boleh ada 1 tahun ajaran Aktif.
     * Kalau id ini mau diset Aktif, non-aktifkan yang lain dulu.
     */
    public function setAsActive(int $id)
    {
        $this->where('id_tahun_ajaran !=', $id)
             ->where('status', 'Aktif')
             ->set(['status' => 'Tidak Aktif'])
             ->update();

        return $this->update($id, ['status' => 'Aktif']);
    }
}