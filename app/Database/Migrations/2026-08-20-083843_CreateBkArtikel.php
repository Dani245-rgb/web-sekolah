<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBkArtikel extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_artikel' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kategori' => [
                'type'       => 'ENUM',
                'constraint' => ['kesehatan_mental', 'karier', 'tes_minat'],
                'null'       => false,
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
            'konten' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'link_eksternal' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'deadline' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Draft', 'Published'],
                'default'    => 'Draft',
                'null'       => false,
            ],
            'id_guru_penulis' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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

        $this->forge->addKey('id_artikel', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addForeignKey('id_guru_penulis', 'guru', 'id_guru', 'SET NULL', 'CASCADE');
        $this->forge->createTable('bk_artikel');
    }

    public function down()
    {
        $this->forge->dropTable('bk_artikel');
    }
}