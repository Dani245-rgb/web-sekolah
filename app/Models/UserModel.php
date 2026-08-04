<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id_user';
    protected $allowedFields = [
        'role_id',
        'username',
        'password',
        'status',
        'must_change_password',
        'login_attempts',
        'locked_until',
    ];
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    /**
     * Cari user berdasarkan username, sekaligus ambil nama role-nya.
     * Dipakai saat proses login (Tahap 10).
     */
    public function findByUsername(string $username)
    {
        return $this->select('users.*, roles.nama_role')
                    ->join('roles', 'roles.id_role = users.role_id')
                    ->where('users.username', $username)
                    ->first();
    }

    /**
     * Verifikasi username + password.
     * Return data user (dengan nama_role) kalau cocok, null kalau tidak.
     */
    public function verifyCredentials(string $username, string $password)
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return null;
        }

        if ($user['status'] !== 'Aktif') {
            return null; // akun dinonaktifkan (misal siswa sudah lulus/pindah)
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }
}