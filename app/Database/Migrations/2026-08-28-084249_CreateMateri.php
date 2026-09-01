<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMateri extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_materi'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_jadwal'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_guru'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'judul'        => ['type' => 'VARCHAR', 'constraint' => 150],
            'deskripsi'    => ['type' => 'TEXT', 'null' => true],
            'file_materi'  => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_materi', true);
        $this->forge->addForeignKey('id_jadwal', 'jadwal', 'id_jadwal', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_guru', 'guru', 'id_guru', 'CASCADE', 'CASCADE');
        $this->forge->createTable('materi');
    }

    public function down()
    {
        $this->forge->dropTable('materi');
    }
}