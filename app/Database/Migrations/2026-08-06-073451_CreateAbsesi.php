<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAbsensi extends Migration
{
    public function up()
    {
        // Header - 1 baris = 1 jadwal yang diabsen di 1 tanggal tertentu
        $this->forge->addField([
            'id_absensi_jadwal' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_jadwal'         => ['type' => 'INT', 'unsigned' => true],
            'tanggal'           => ['type' => 'DATE'],
            'id_guru'           => ['type' => 'INT', 'unsigned' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_absensi_jadwal', true);
        $this->forge->addUniqueKey(['id_jadwal', 'tanggal'], 'uq_jadwal_tanggal');
        $this->forge->addForeignKey('id_jadwal', 'jadwal', 'id_jadwal', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_guru', 'guru', 'id_guru', 'CASCADE', 'CASCADE');
        $this->forge->createTable('absensi_jadwal');

        // Detail - per siswa, status kehadiran
        $this->forge->addField([
            'id_absensi_detail' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'id_absensi_jadwal' => ['type' => 'INT', 'unsigned' => true],
            'id_siswa'          => ['type' => 'INT', 'unsigned' => true],
            'status'            => ['type' => 'ENUM', 'constraint' => ['Hadir', 'Izin', 'Sakit', 'Alfa'], 'default' => 'Hadir'],
            'keterangan'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id_absensi_detail', true);
        $this->forge->addUniqueKey(['id_absensi_jadwal', 'id_siswa'], 'uq_absensi_siswa');
        $this->forge->addForeignKey('id_absensi_jadwal', 'absensi_jadwal', 'id_absensi_jadwal', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->createTable('absensi_detail');
    }

    public function down()
    {
        $this->forge->dropTable('absensi_detail');
        $this->forge->dropTable('absensi_jadwal');
    }
}