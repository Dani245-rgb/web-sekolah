<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTahunAjaran extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tahun_ajaran'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'semester'        => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap'], 'null' => true],
            'tanggal_mulai'   => ['type' => 'DATE', 'null' => true],
            'tanggal_selesai' => ['type' => 'DATE', 'null' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Tidak Aktif', 'Belum Aktif'], 'default' => 'Belum Aktif'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_tahun_ajaran', true);
        $this->forge->createTable('tahun_ajaran');
    }

    public function down()
    {
        $this->forge->dropTable('tahun_ajaran');
    }
}