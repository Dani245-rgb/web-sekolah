<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/**
 * Role akun login: Admin dan Content Manager.
 * Role Guru dan Siswa dihapus (data master guru/siswa tidak disentuh).
 *
 * Pengaman: kalau masih ada user yang memakai role Guru/Siswa, migration berhenti
 * dan tidak mengubah apa pun. Pada instalasi baru (tabel roles masih kosong),
 * migration tidak mengisi apa-apa; RoleSeeder yang mengisi.
 */
class UbahRoleGuruSiswaMenjadiContentManager extends Migration
{
    public function up()
    {
        if ($this->db->table('roles')->countAllResults() === 0) {
            return;
        }

        $idLama = array_column(
            $this->db->table('roles')->select('id_role')->whereIn('nama_role', ['Guru', 'Siswa'])->get()->getResultArray(),
            'id_role'
        );

        if ($idLama !== []) {
            $dipakai = $this->db->table('users')->whereIn('role_id', $idLama)->countAllResults();

            if ($dipakai > 0) {
                throw new RuntimeException("Masih ada {$dipakai} user dengan role Guru/Siswa. Migration dibatalkan.");
            }

            $this->db->table('roles')->whereIn('id_role', $idLama)->delete();
        }

        if ($this->db->table('roles')->where('nama_role', 'Content Manager')->countAllResults() === 0) {
            $this->db->table('roles')->insert(['nama_role' => 'Content Manager']);
        }
    }

    public function down()
    {
        $dipakai = $this->db->table('users')
            ->join('roles', 'roles.id_role = users.role_id')
            ->where('roles.nama_role', 'Content Manager')
            ->countAllResults();

        if ($dipakai > 0) {
            throw new RuntimeException("Masih ada {$dipakai} user Content Manager. Rollback dibatalkan.");
        }

        $this->db->table('roles')->where('nama_role', 'Content Manager')->delete();

        foreach (['Guru', 'Siswa'] as $nama) {
            if ($this->db->table('roles')->where('nama_role', $nama)->countAllResults() === 0) {
                $this->db->table('roles')->insert(['nama_role' => $nama]);
            }
        }
    }
}