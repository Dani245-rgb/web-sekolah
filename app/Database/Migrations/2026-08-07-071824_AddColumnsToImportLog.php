<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnsToImportLog extends Migration
{
    public function up()
    {
        $this->forge->addColumn('import_log', [
            'jenis_import' => [
                'type'       => 'ENUM',
                'constraint' => ['siswa', 'guru', 'jadwal', 'nilai'],
                'null'       => true,
                'after'      => 'id_import_log',
            ],
            'id_referensi' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'jenis_import',
            ],
            'id_user' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'id_referensi',
            ],
            'total_ditimpa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'total_sukses',
            ],
        ]);

        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'SET NULL', 'import_log');
        $this->db->query('ALTER TABLE import_log ADD KEY idx_jenis_import (jenis_import)');
    }

    public function down()
    {
        $this->forge->dropForeignKey('import_log', 'import_log_id_user_foreign');
        $this->forge->dropColumn('import_log', ['jenis_import', 'id_referensi', 'id_user', 'total_ditimpa']);
    }
}