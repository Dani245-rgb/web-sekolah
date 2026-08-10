<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMutasi extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_mutasi'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'jenis_mutasi'    => ['type' => 'ENUM', 'constraint' => ['Pindah', 'Keluar']],
            'tanggal_mutasi'  => ['type' => 'DATE'],
            'sekolah_tujuan'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'alasan'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id_mutasi');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey('id_siswa'); // 1 siswa cuma bisa punya 1 record mutasi aktif
        $this->forge->createTable('mutasi');
    }

    public function down()
    {
        $this->forge->dropTable('mutasi');
    }
}