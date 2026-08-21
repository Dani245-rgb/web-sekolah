<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'deleted_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'locked_until'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'deleted_at');
    }
}