<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKeteranganToKomponenNilai extends Migration
{
    public function up()
    {
        $this->forge->addColumn('komponen_nilai', [
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'link_referensi',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('komponen_nilai', 'keterangan');
    }
}