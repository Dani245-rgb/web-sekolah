<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEkstrakurikulerTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Published', 'Draft'],
                'null'       => false,
                'default'    => 'Published',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('ekstrakurikuler');
    }

    public function down()
    {
        $this->forge->dropTable('ekstrakurikuler');
    }
}