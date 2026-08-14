<?php

namespace App\Models;

use CodeIgniter\Model;

class NotifikasiModel extends Model
{
    protected $table         = 'notifikasi';
    protected $primaryKey    = 'id_notif';
    protected $allowedFields = ['user_id', 'judul', 'pesan', 'jenis', 'link', 'is_read', 'created_at'];
    protected $returnType    = 'array';

    /**
     * Buat notifikasi baru.
     * $userId = null artinya broadcast, tampil ke semua Admin.
     */
    public function buat(?int $userId, string $judul, string $jenis, ?string $pesan = null, ?string $link = null)
    {
        return $this->insert([
            'user_id'    => $userId,
            'judul'      => $judul,
            'pesan'      => $pesan,
            'jenis'      => $jenis,
            'link'       => $link,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Ambil notifikasi untuk user tertentu (termasuk yang broadcast/null).
     */
    public function getForUser(int $userId, int $limit = 10)
    {
        return $this->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id', null)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);
    }

    public function countUnread(int $userId): int
    {
        return $this->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id', null)
            ->groupEnd()
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function tandaiDibaca(int $idNotif)
    {
        return $this->update($idNotif, ['is_read' => 1]);
    }

    public function tandaiSemuaDibaca(int $userId)
    {
        return $this->groupStart()
                ->where('user_id', $userId)
                ->orWhere('user_id', null)
            ->groupEnd()
            ->where('is_read', 0)
            ->set('is_read', 1)
            ->update();
    }
}