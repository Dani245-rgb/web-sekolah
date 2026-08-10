<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdJurusanToKelas extends Migration
{
    public function up()
    {
        $this->forge->addColumn('kelas', [
            'id_jurusan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // nullable dulu, biar kelas lama yang belum punya jurusan gak error
                'after'      => 'nama_kelas',
            ],
        ]);

        $this->forge->addForeignKey('id_jurusan', 'jurusan', 'id_jurusan', 'CASCADE', 'SET NULL');
        $this->forge->processIndexes('kelas');
    }

    public function down()
    {
        $this->forge->dropForeignKey('kelas', 'kelas_id_jurusan_foreign');
        $this->forge->dropColumn('kelas', 'id_jurusan');
    }
}