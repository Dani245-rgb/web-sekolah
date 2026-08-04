<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_log' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'username'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'aksi'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id_log');
        $this->forge->addKey('user_id');
        $this->forge->createTable('audit_log');
    }

    public function down()
    {
        $this->forge->dropTable('audit_log');
    }
}