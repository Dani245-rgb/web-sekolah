<?php

namespace App\Models;

use CodeIgniter\Model;

class HariKhususModel extends Model
{
    protected $table         = 'hari_khusus';
    protected $primaryKey    = 'id_hari_khusus';
    protected $allowedFields = ['tanggal', 'keterangan', 'id_jadwal', 'id_guru', 'created_at'];
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    /**
     * Cek apakah tanggal tertentu = hari khusus untuk jadwal ini.
     * Berlaku kalau: id_jadwal = jadwal ini (guru) ATAU id_jadwal null (global/Admin).
     */
    public function cariByTanggalJadwal($idJadwal, $tanggal)
    {
        return $this->where('tanggal', $tanggal)
            ->groupStart()
                ->where('id_jadwal', $idJadwal)
                ->orWhere('id_jadwal', null)
            ->groupEnd()
            ->orderBy('id_jadwal', 'ASC')
            ->first();
    }

    /**
     * Ambil semua hari khusus dalam list tanggal untuk jadwal ini (global + spesifik),
     * hasilnya map [tanggal => keterangan].
     */
    public function getMapByTanggalList($idJadwal, array $tanggalList)
    {
        if (empty($tanggalList)) return [];

        $rows = $this->whereIn('tanggal', $tanggalList)
            ->groupStart()
                ->where('id_jadwal', $idJadwal)
                ->orWhere('id_jadwal', null)
            ->groupEnd()
            ->orderBy('id_jadwal', 'ASC')
            ->findAll();

        $map = [];
        foreach ($rows as $r) {
            // kalau ada global & spesifik di tanggal sama, yg spesifik menang
            if (!isset($map[$r['tanggal']]) || $r['id_jadwal'] !== null) {
                $map[$r['tanggal']] = $r['keterangan'];
            }
        }
        return $map;
    }
}