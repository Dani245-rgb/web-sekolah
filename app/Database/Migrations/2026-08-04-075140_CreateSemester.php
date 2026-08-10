<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSemester extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_semester' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_semester'   => ['type' => 'ENUM', 'constraint' => ['Ganjil', 'Genap']],
            'tanggal_mulai'   => ['type' => 'DATE'],
            'tanggal_selesai' => ['type' => 'DATE'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Nonaktif'], 'default' => 'Nonaktif'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id_semester');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['id_tahun_ajaran', 'nama_semester'], 'unik_semester_per_tahun');
        $this->forge->createTable('semester');
    }

    public function down()
    {
        $this->forge->dropTable('semester');
    }
}