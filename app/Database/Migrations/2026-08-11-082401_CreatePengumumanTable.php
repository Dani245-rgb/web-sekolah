<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePengumumanTable extends Migration
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
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'isi' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'tanggal_publish' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Published', 'Draft'],
                'null'       => false,
                'default'    => 'Draft',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('pengumuman');
    }

    public function down()
    {
        $this->forge->dropTable('pengumuman');
    }
}