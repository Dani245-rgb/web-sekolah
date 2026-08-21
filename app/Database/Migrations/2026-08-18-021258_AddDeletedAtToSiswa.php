<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToSiswa extends Migration
{
    public function up()
    {
        // Kolom deleted_at sudah ada di tabel siswa sebelumnya, tidak perlu ditambah lagi.
    }

    public function down()
    {
        // Kosongkan juga, supaya rollback tidak error
    }
}