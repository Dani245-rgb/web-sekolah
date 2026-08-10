<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKategoriNilai extends Migration
{
    public function up()
    {
        // 1. Tabel baru: kategori_nilai
        $this->forge->addField([
            'id_kategori' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_pengaturan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'bobot' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_kategori', true);
        $this->forge->addForeignKey('id_pengaturan', 'pengaturan_nilai', 'id_pengaturan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kategori_nilai');

        // 2. Tambah kolom id_kategori ke komponen_nilai (nullable dulu, biar data lama gak error)
        $this->forge->addColumn('komponen_nilai', [
            'id_kategori' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id_pengaturan',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('komponen_nilai', 'id_kategori');
        $this->forge->dropTable('kategori_nilai');
    }
}