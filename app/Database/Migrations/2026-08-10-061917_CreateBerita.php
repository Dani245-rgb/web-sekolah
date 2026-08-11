<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBerita extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_berita'       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'judul'           => ['type' => 'VARCHAR', 'constraint' => 200],
            'slug'            => ['type' => 'VARCHAR', 'constraint' => 220],
            'kategori'        => ['type' => 'ENUM', 'constraint' => ['Akademik', 'Prestasi', 'Kegiatan', 'Umum']],
            'gambar'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'ringkasan'       => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'konten'          => ['type' => 'TEXT'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Draft', 'Published'], 'default' => 'Draft'],
            'tanggal_publish' => ['type' => 'DATE', 'null' => true],
            'id_user'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_berita', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('status');
        $this->forge->createTable('berita');
    }

    public function down()
    {
        $this->forge->dropTable('berita');
    }
}