<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKomponenNilaiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_komponen'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_pengaturan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_komponen' => ['type' => 'VARCHAR', 'constraint' => 50],
            'bobot'         => ['type' => 'INT', 'constraint' => 11],
            'urutan'        => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
        ]);

        $this->forge->addKey('id_komponen', true);
        $this->forge->addForeignKey('id_pengaturan', 'pengaturan_nilai', 'id_pengaturan', 'CASCADE', 'CASCADE');

        $this->forge->createTable('komponen_nilai');
    }

    public function down()
    {
        $this->forge->dropTable('komponen_nilai');
    }
}