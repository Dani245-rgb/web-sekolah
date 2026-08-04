<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueToKelasSiswa extends Migration
{
    public function up()
    {
        $this->forge->addUniqueKey(['id_siswa', 'id_tahun_ajaran']);
        $this->forge->processIndexes('kelas_siswa');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE kelas_siswa DROP INDEX id_siswa_id_tahun_ajaran');
    }
}