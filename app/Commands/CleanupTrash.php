<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\SiswaModel;
use App\Models\UserModel;
use Config\Database;

class CleanupTrash extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'cleanup:trash';
    protected $description = 'Hapus permanen data siswa di tong sampah yang sudah lebih dari 30 hari.';

    public function run(array $params)
    {
        $siswaModel = new SiswaModel();
        $userModel  = new UserModel();
        $batasWaktu = date('Y-m-d H:i:s', strtotime('-30 days'));

        $expired = $siswaModel->onlyDeleted()
            ->where('deleted_at <', $batasWaktu)
            ->findAll();

        if (empty($expired)) {
            CLI::write('Tidak ada data yang perlu dibersihkan.', 'green');
            return;
        }

        $db = Database::connect();

        foreach ($expired as $s) {
            $db->transStart();

            $db->table('kelas_siswa')->where('id_siswa', $s['id_siswa'])->delete();
            $siswaModel->delete($s['id_siswa'], true); // hard delete
            $userModel->delete($s['user_id'], true);

            $db->transComplete();

            if (!empty($s['foto']) && file_exists(FCPATH . 'uploads/siswa/' . $s['foto'])) {
                unlink(FCPATH . 'uploads/siswa/' . $s['foto']);
            }

            CLI::write("Dihapus permanen: {$s['nama']} (NIS: {$s['nis']})", 'yellow');
        }

        CLI::write(count($expired) . ' data siswa berhasil dibersihkan permanen.', 'green');
    }
}