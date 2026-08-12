<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrestasiTable extends Migration
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
            'tim' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tingkat' => [
                'type'       => 'ENUM',
                'constraint' => ['Kabupaten', 'Provinsi', 'Nasional', 'Internasional'],
                'null'       => false,
                'default'    => 'Kabupaten',
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'tanggal' => [
                'type' => 'DATE',
                'null' => false,
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
        $this->forge->createTable('prestasi');
    }

    public function down()
    {
        $this->forge->dropTable('prestasi');
    }
}