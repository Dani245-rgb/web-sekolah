<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGaleriTable extends Migration
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
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'kategori' => [
                'type'       => 'ENUM',
                'constraint' => ['Kegiatan', 'Fasilitas', 'Prestasi', 'Lainnya'],
                'null'       => false,
                'default'    => 'Kegiatan',
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
        $this->forge->createTable('galeri');
    }

    public function down()
    {
        $this->forge->dropTable('galeri');
    }
}