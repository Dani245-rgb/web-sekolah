<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePendaftarPpdbTable extends Migration
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
            'nama_lengkap' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'tempat_lahir' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['Laki-laki', 'Perempuan'],
                'null'       => false,
            ],
            'asal_sekolah' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'jurusan_pilihan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'alamat' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Menunggu', 'Diterima', 'Ditolak'],
                'null'       => false,
                'default'    => 'Menunggu',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('pendaftar_ppdb');
    }

    public function down()
    {
        $this->forge->dropTable('pendaftar_ppdb');
    }
}