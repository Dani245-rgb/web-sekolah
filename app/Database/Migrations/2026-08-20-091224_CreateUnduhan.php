<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnduhan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_unduhan' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'nama_file_asli' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Nama file asli sebelum di-random, dipakai saat download',
            ],
            'ukuran_file' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'comment'  => 'Ukuran file dalam byte',
            ],
            'ekstensi' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'jumlah_unduh' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
                'null'     => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Draft', 'Published'],
                'default'    => 'Draft',
                'null'       => false,
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

        $this->forge->addKey('id_unduhan', true);
        $this->forge->addKey('kategori');
        $this->forge->createTable('unduhan');
    }

    public function down()
    {
        $this->forge->dropTable('unduhan');
    }
}