<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePengaturanNilaiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pengaturan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_guru'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_kelas'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_mapel'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_semester'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kkm'           => ['type' => 'INT', 'constraint' => 11, 'default' => 75],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_pengaturan', true);
        $this->forge->addUniqueKey(['id_guru', 'id_kelas', 'id_mapel', 'id_semester'], 'uniq_pengaturan_nilai');

        $this->forge->addForeignKey('id_guru', 'guru', 'id_guru', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id_kelas', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_mapel', 'mapel', 'id_mapel', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_semester', 'semester', 'id_semester', 'CASCADE', 'CASCADE');

        $this->forge->createTable('pengaturan_nilai');
    }

    public function down()
    {
        $this->forge->dropTable('pengaturan_nilai');
    }
}