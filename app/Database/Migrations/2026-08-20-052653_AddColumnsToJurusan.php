<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColumnsToJurusan extends Migration
{
    public function up()
    {
        $this->forge->addColumn('jurusan', [
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'nama_jurusan',
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'slug',
            ],
            'foto' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'deskripsi',
            ],
            'kompetensi' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'foto',
            ],
            'prospek_kerja' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'kompetensi',
            ],
        ]);

        // Isi slug untuk baris lama yang mungkin sudah ada, biar tidak NULL
        $db = \Config\Database::connect();
        $rows = $db->table('jurusan')->select('id_jurusan, nama_jurusan')->get()->getResultArray();
        foreach ($rows as $row) {
            $slug = url_title($row['nama_jurusan'], '-', true);
            $db->table('jurusan')->where('id_jurusan', $row['id_jurusan'])->update(['slug' => $slug]);
        }

        $db->query('ALTER TABLE jurusan ADD UNIQUE unique_slug (slug)');
    }

    public function down()
    {
        $this->forge->dropColumn('jurusan', ['slug', 'deskripsi', 'foto', 'kompetensi', 'prospek_kerja']);
    }
}
