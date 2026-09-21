<?php

namespace App\Models;

use CodeIgniter\Model;

class OrganisasiModel extends BaseModel
{
    protected $table            = 'organisasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nama', 'deskripsi', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nama'   => 'required|min_length[2]|max_length[100]',
        'status' => 'required|in_list[Published,Draft]',
    ];

    public function getPublishedWithAnggota()
    {
        $organisasiList = $this->where('status', 'Published')->orderBy('nama', 'ASC')->findAll();
        $anggotaModel   = new AnggotaOrganisasiModel();

        foreach ($organisasiList as &$org) {
            $org['anggota'] = $anggotaModel->where('id_organisasi', $org['id'])
                ->orderBy('urutan', 'ASC')
                ->findAll();
        }

        return $organisasiList;
    }
}