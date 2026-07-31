<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGuru extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_guru'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nip'           => ['type' => 'VARCHAR', 'constraint' => 30],
            'nuptk'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'nama'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'tempat_lahir'  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'tanggal_lahir' => ['type' => 'DATE', 'null' => true],
            'jenis_kelamin' => ['type' => 'ENUM', 'constraint' => ['L', 'P']],
            'jabatan'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'no_hp'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'foto'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'        => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Nonaktif'], 'default' => 'Aktif'],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_guru', true);
        $this->forge->addUniqueKey('user_id');
        $this->forge->addUniqueKey('nip');
        $this->forge->addForeignKey('user_id', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('guru');
    }

    public function down()
    {
        $this->forge->dropTable('guru');
    }
}