<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNilaiSiswaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_nilai'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_komponen' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_siswa'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nilai'       => ['type' => 'DECIMAL', 'constraint' => '5,2'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_nilai', true);
        $this->forge->addUniqueKey(['id_komponen', 'id_siswa'], 'uniq_nilai_siswa');

        $this->forge->addForeignKey('id_komponen', 'komponen_nilai', 'id_komponen', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');

        $this->forge->createTable('nilai_siswa');
    }

    public function down()
    {
        $this->forge->dropTable('nilai_siswa');
    }
}