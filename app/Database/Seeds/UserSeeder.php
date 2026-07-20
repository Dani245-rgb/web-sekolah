<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Ambil id_role untuk Admin
        $adminRole = $this->db->table('roles')
                              ->where('nama_role', 'Admin')
                              ->get()
                              ->getRow();

        $data = [
            'role_id'  => $adminRole->id_role,
            'username' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT), // ganti sebelum production
            'status'   => 'Aktif',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($data);
    }
}