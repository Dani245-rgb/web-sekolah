<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePklTables extends Migration
{
    public function up()
    {
        // Tabel pkl_penempatan
        $this->forge->addField([
            'id_penempatan' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_siswa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_guru_pembimbing' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nama_perusahaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'alamat_perusahaan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'nama_pembimbing_industri' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'no_hp_pembimbing_industri' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Aktif', 'Selesai', 'Dibatalkan'],
                'default'    => 'Aktif',
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_penempatan', true);
        $this->forge->addKey('id_siswa');
        $this->forge->addKey('id_guru_pembimbing');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_guru_pembimbing', 'guru', 'id_guru', 'SET NULL', 'CASCADE');
        $this->forge->createTable('pkl_penempatan');

        // Tabel pkl_jurnal
        $this->forge->addField([
            'id_jurnal' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_penempatan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tanggal' => [
                'type' => 'DATE',
            ],
            'kegiatan' => [
                'type' => 'TEXT',
            ],
            'kendala' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status_validasi' => [
                'type'       => 'ENUM',
                'constraint' => ['Menunggu', 'Disetujui', 'Ditolak'],
                'default'    => 'Menunggu',
            ],
            'catatan_pembimbing' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_jurnal', true);
        $this->forge->addKey('id_penempatan');
        $this->forge->addUniqueKey(['id_penempatan', 'tanggal']); // 1 jurnal per hari per penempatan
        $this->forge->addForeignKey('id_penempatan', 'pkl_penempatan', 'id_penempatan', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pkl_jurnal');
    }

    public function down()
    {
        $this->forge->dropTable('pkl_jurnal', true);
        $this->forge->dropTable('pkl_penempatan', true);
    }
}