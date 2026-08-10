<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexAuditLogCreatedAt extends Migration
{
    public function up()
    {
        $this->forge->addKey('created_at', false, false, 'idx_audit_created_at');
        $this->forge->processIndexes('audit_log');
    }

    public function down()
    {
        $this->forge->dropKey('audit_log', 'idx_audit_created_at');
    }
}