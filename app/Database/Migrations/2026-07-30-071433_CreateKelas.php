<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKelas extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kelas'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tingkat'         => ['type' => 'ENUM', 'constraint' => ['X', 'XI', 'XII']],
            'jurusan'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'rombel'          => ['type' => 'INT', 'constraint' => 3, 'default' => 1],
            'nama_kelas'      => ['type' => 'VARCHAR', 'constraint' => 50],
            'wali_kelas_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'ruangan'         => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'kapasitas'       => ['type' => 'INT', 'constraint' => 3, 'default' => 36],
            'id_tahun_ajaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Nonaktif'], 'default' => 'Aktif'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_kelas', true);
        $this->forge->addForeignKey('wali_kelas_id', 'guru', 'id_guru', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('id_tahun_ajaran', 'tahun_ajaran', 'id_tahun_ajaran', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('kelas');
    }

    public function down()
    {
        $this->forge->dropTable('kelas');
    }
}