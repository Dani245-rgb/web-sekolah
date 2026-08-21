<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSingkatanToJurusan extends Migration
{
    public function up()
    {
        $this->forge->addColumn('jurusan', [
            'singkatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'nama_jurusan',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('jurusan', 'singkatan');
    }
}