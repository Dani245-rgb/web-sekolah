<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSiswa extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_siswa'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nis'            => ['type' => 'VARCHAR', 'constraint' => 20],
            'nisn'           => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama'           => ['type' => 'VARCHAR', 'constraint' => 100],
            'tempat_lahir'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'tanggal_lahir'  => ['type' => 'DATE', 'null' => true],
            'jenis_kelamin'  => ['type' => 'ENUM', 'constraint' => ['L', 'P']],
            'agama'          => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'alamat'         => ['type' => 'TEXT', 'null' => true],
            'nama_ayah'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'nama_ibu'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'pekerjaan_ortu' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'no_hp_ortu'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'email'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'foto'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Lulus', 'Pindah', 'Keluar'], 'default' => 'Aktif'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_siswa', true);
        $this->forge->addUniqueKey('nis');
        $this->forge->addUniqueKey('nisn');
        $this->forge->addForeignKey('user_id', 'users', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('siswa');
    }

    public function down()
    {
        $this->forge->dropTable('siswa');
    }
}