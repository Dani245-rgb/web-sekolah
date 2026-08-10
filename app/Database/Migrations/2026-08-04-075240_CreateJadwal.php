<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJadwal extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_jadwal'       => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_semester'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_kelas'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_mapel'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_guru'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_ruangan'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'hari'            => ['type' => 'ENUM', 'constraint' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']],
            'jam_mulai'       => ['type' => 'TIME'],
            'jam_selesai'     => ['type' => 'TIME'],
            'status'          => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Nonaktif'], 'default' => 'Aktif'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id_jadwal');
        $this->forge->addForeignKey('id_semester', 'semester', 'id_semester', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_kelas', 'kelas', 'id_kelas', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_mapel', 'mapel', 'id_mapel', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_guru', 'guru', 'id_guru', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_ruangan', 'ruangan', 'id_ruangan', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['id_kelas', 'hari', 'jam_mulai', 'id_semester'], 'unik_slot_kelas');
        $this->forge->createTable('jadwal');
    }

    public function down()
    {
        $this->forge->dropTable('jadwal');
    }
}