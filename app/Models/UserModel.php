<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends BaseModel
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
    protected $useSoftDeletes = true;

    /**
     * Cari user berdasarkan username, sekaligus ambil nama role-nya.
     * Dipakai saat proses login (Tahap 10).
     * 
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

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        if ($user['status'] !== 'Aktif') {
            return 'inactive'; // password benar, tapi akun nonaktif
        }

        return $user;
    }

    /**
     * Ambil semua user lintas role, dengan nama tampilan
     * (nama guru/siswa kalau ada, atau username untuk admin).
     */
    public function getAllWithDetail(int $perPage = 100, ?string $keyword = null, ?int $roleId = null, string $pagerGroup = 'user')
    {
        $builder = $this->select("
                users.id_user, users.username, users.status, users.role_id,
                users.login_attempts, users.locked_until,
                roles.nama_role,
                COALESCE(guru.nama, siswa.nama, users.username) as nama_tampil
            ")
            ->join('roles', 'roles.id_role = users.role_id')
            ->join('guru', 'guru.user_id = users.id_user', 'left')
            ->join('siswa', 'siswa.user_id = users.id_user', 'left');

        if (!empty($roleId)) {
            $builder->where('users.role_id', $roleId);
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('users.username', $keyword)
                ->orLike('guru.nama', $keyword)
                ->orLike('siswa.nama', $keyword)
                ->orLike('roles.nama_role', $keyword)
                ->groupEnd();
        }

        return $builder
            ->orderBy('roles.nama_role', 'ASC')
            ->orderBy('nama_tampil', 'ASC')
            ->paginate($perPage, $pagerGroup);
    }
}
