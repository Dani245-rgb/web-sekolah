<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToSiswa extends Migration
{
    public function up()
    {
        $this->forge->addColumn('siswa', [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('siswa', 'deleted_at');
    }
}