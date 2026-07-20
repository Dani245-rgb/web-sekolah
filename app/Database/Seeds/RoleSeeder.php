<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['nama_role' => 'Admin'],
            ['nama_role' => 'Guru'],
            ['nama_role' => 'Siswa'],
        ];

        $this->db->table('roles')->insertBatch($data);
    }
}