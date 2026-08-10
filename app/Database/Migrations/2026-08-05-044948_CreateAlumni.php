<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlumni extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_alumni'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_siswa'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal_lulus'   => ['type' => 'DATE'],
            'no_ijazah'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'keterangan'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id_alumni');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey('id_siswa'); // 1 siswa cuma bisa jadi alumni sekali
        $this->forge->createTable('alumni');
    }

    public function down()
    {
        $this->forge->dropTable('alumni');
    }
}