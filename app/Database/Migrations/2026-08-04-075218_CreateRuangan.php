<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRuangan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_ruangan'   => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_ruangan' => ['type' => 'VARCHAR', 'constraint' => 50],
            'kapasitas'    => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Nonaktif'], 'default' => 'Aktif'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id_ruangan');
        $this->forge->addUniqueKey('nama_ruangan');
        $this->forge->createTable('ruangan');
    }

    public function down()
    {
        $this->forge->dropTable('ruangan');
    }
}