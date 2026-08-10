<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueKeyKelasSiswa extends Migration
{
    public function up()
    {
        $this->forge->addUniqueKey(['id_siswa', 'id_tahun_ajaran'], 'uq_siswa_tahun');
        $this->forge->processIndexes('kelas_siswa');
    }

    public function down()
    {
        $this->forge->dropKey('kelas_siswa', 'uq_siswa_tahun');
    }
}