<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTugasTables extends Migration
{
    public function up()
    {
        // Tabel tugas
        $this->forge->addField([
            'id_tugas' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_jadwal' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_guru' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_komponen' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_lampiran' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'tanggal_mulai' => [
                'type' => 'DATETIME',
            ],
            'tenggat' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Aktif', 'Ditutup'],
                'default'    => 'Aktif',
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_tugas', true);
        $this->forge->addKey('id_jadwal');
        $this->forge->addKey('id_guru');
        $this->forge->addKey('id_komponen');
        $this->forge->addForeignKey('id_jadwal', 'jadwal', 'id_jadwal', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_guru', 'guru', 'id_guru', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_komponen', 'komponen_nilai', 'id_komponen', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tugas');

        // Tabel tugas_submisi
        $this->forge->addField([
            'id_submisi' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_tugas' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_siswa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'isi_jawaban' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_jawaban' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'waktu_kumpul' => [
                'type' => 'DATETIME',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Menunggu', 'Dinilai', 'Terlambat'],
                'default'    => 'Menunggu',
            ],
            'nilai' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'catatan_guru' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_submisi', true);
        $this->forge->addKey('id_tugas');
        $this->forge->addKey('id_siswa');
        $this->forge->addUniqueKey(['id_tugas', 'id_siswa']); // 1 siswa cuma 1 submisi per tugas
        $this->forge->addForeignKey('id_tugas', 'tugas', 'id_tugas', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_siswa', 'siswa', 'id_siswa', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tugas_submisi');
    }

    public function down()
    {
        $this->forge->dropTable('tugas_submisi', true);
        $this->forge->dropTable('tugas', true);
    }
}