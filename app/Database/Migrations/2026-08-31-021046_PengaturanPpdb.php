<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePengaturanPpdbTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['buka', 'tutup'],
                'default' => 'tutup',
            ],
            'pesan_tutup' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tahun_ajaran' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('pengaturan_ppdb');

        // seed 1 baris default supaya nggak perlu cek "kalau kosong"
        $this->db->table('pengaturan_ppdb')->insert([
            'status'       => 'tutup',
            'pesan_tutup'  => 'Pendaftaran siswa baru belum dibuka. Silakan cek kembali nanti.',
            'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('pengaturan_ppdb');
    }
}