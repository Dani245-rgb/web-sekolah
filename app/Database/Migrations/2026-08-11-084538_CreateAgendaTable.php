<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgendaTable extends Migration
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
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'waktu' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'lokasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->createTable('agenda');
    }

    public function down()
    {
        $this->forge->dropTable('agenda');
    }
}