<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AuditLogModel;

class CleanupAuditLog extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'cleanup:auditlog';
    protected $description = 'Hapus permanen audit log yang usianya lebih dari 6 bulan.';

    public function run(array $params)
    {
        $auditLogModel = new AuditLogModel();
        $batasWaktu    = date('Y-m-d H:i:s', strtotime('-6 months'));

        $jumlahLama = $auditLogModel->where('created_at <', $batasWaktu)->countAllResults();

        if ($jumlahLama === 0) {
            CLI::write('Tidak ada log lama yang perlu dibersihkan.', 'green');
            return;
        }

        $auditLogModel->where('created_at <', $batasWaktu)->delete();

        CLI::write("{$jumlahLama} baris audit log (lebih dari 6 bulan) berhasil dihapus.", 'yellow');
    }
}