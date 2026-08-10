<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiRollbackStatusToImportLog extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE import_log MODIFY status ENUM('Proses','Selesai','Gagal','di-rollback') NOT NULL DEFAULT 'Proses'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE import_log MODIFY status ENUM('Proses','Selesai','Gagal') NOT NULL DEFAULT 'Proses'");
    }
}