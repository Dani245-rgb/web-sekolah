<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoginAttemptsToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'login_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'must_change_password',
            ],
            'locked_until' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'login_attempts',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['login_attempts', 'locked_until']);
    }
}