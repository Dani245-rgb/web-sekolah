<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueToNilaiSiswa extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE nilai_siswa ADD UNIQUE KEY unique_komponen_siswa (id_komponen, id_siswa)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE nilai_siswa DROP INDEX unique_komponen_siswa');
    }
}