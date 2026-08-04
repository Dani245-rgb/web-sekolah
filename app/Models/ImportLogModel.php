<?php

namespace App\Models;

use CodeIgniter\Model;

class ImportLogModel extends Model
{
    protected $table            = 'import_log';
    protected $primaryKey       = 'id_import_log';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'nama_file', 'path_file', 'total_baris', 'baris_selesai',
        'total_sukses', 'total_gagal', 'status', 'detail_gagal',
    ];

    // Cari job yang masih "Proses" (buat deteksi resume)
    public function getJobBelumSelesai()
    {
        return $this->where('status', 'Proses')
                    ->orderBy('id_import_log', 'DESC')
                    ->first();
    }
}