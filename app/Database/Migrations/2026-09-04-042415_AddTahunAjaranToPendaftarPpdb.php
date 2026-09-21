<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTahunAjaranToPendaftarPpdb extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pendaftar_ppdb', [
            'tahun_ajaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'jurusan_pilihan',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pendaftar_ppdb', 'tahun_ajaran');
    }
}