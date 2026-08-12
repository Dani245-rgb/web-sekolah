<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePesanKontakTable extends Migration
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
                'constraint' => 255,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'subjek' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'pesan' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Belum Dibaca', 'Sudah Dibaca'],
                'null'       => false,
                'default'    => 'Belum Dibaca',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('pesan_kontak');
    }

    public function down()
    {
        $this->forge->dropTable('pesan_kontak');
    }
}