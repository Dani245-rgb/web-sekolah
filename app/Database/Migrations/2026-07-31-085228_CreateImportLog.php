<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateImportLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_import_log' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_file'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'path_file'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'total_baris'    => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'baris_selesai'  => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total_sukses'   => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'total_gagal'    => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Proses', 'Selesai', 'Gagal'], 'default' => 'Proses'],
            'detail_gagal'   => ['type' => 'LONGTEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_import_log', true);
        $this->forge->createTable('import_log');
    }

    public function down()
    {
        $this->forge->dropTable('import_log');
    }
}