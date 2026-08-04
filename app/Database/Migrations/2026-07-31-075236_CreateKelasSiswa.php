<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKelasSiswa extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kelas_siswa'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_kelas'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_siswa'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_kelas_siswa', true);
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id_kelas', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('kelas_siswa');
    }

    public function down()
    {
        $this->forge->dropTable('kelas_siswa');
    }
}