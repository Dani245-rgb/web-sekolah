<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnsToAuditLog extends Migration
{
    public function up()
    {
        $this->forge->addColumn('audit_log', [
            'tabel_target' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'aksi',
            ],
            'id_record_target' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'tabel_target',
            ],
            'nilai_lama' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'id_record_target',
            ],
            'nilai_baru' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'nilai_lama',
            ],
            'id_import_log' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'nilai_baru',
            ],
        ]);

        $this->forge->addForeignKey('id_import_log', 'import_log', 'id_import_log', 'CASCADE', 'SET NULL', 'audit_log');
        $this->db->query('ALTER TABLE audit_log ADD KEY idx_record_target (tabel_target, id_record_target)');
    }

    public function down()
    {
        $this->forge->dropForeignKey('audit_log', 'audit_log_id_import_log_foreign');
        $this->forge->dropColumn('audit_log', ['tabel_target', 'id_record_target', 'nilai_lama', 'nilai_baru', 'id_import_log']);
    }
}