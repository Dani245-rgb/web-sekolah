<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMapel extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_mapel'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode_mapel'     => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama_mapel'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'kelompok_mapel' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'kkm'            => ['type' => 'INT', 'constraint' => 3, 'default' => 75],
            'semester'       => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap'], 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Nonaktif'], 'default' => 'Aktif'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_mapel', true);
        $this->forge->addUniqueKey('kode_mapel');
        $this->forge->createTable('mapel');
    }

    public function down()
    {
        $this->forge->dropTable('mapel');
    }
}