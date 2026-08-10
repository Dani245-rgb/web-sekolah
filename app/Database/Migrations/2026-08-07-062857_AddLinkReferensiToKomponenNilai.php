<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLinkReferensiToKomponenNilai extends Migration
{
    public function up()
    {
        $this->forge->addColumn('komponen_nilai', [
            'link_referensi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'nama_komponen',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('komponen_nilai', 'link_referensi');
    }
}