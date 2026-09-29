<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * Bikin slug URL yang unik untuk tabel apa pun (jurusan, berita, prestasi, dst).
 */
class SlugService
{
    /**
     * @param string   $teks           Teks sumber, misal nama jurusan
     * @param string   $tabel          Nama tabel yang punya kolom slug
     * @param string   $kolomId        Nama primary key tabel itu
     * @param int|null $excludeId      ID yang dikecualikan (dipakai saat update, biar tidak bentrok sama dirinya sendiri)
     * @param string   $prefixCadangan Dipakai kalau teks isinya karakter aneh semua
     * @param string   $kolomSlug      Nama kolom slug
     */
    public function buatUnik(
        string $teks,
        string $tabel,
        string $kolomId,
        ?int $excludeId = null,
        string $prefixCadangan = 'item',
        string $kolomSlug = 'slug'
    ): string {
        helper('url');

        $slugAsli = url_title($teks, '-', true);

        if ($slugAsli === '') {
            $slugAsli = $prefixCadangan . '-' . time();
        }

        $db      = Database::connect();
        $slug    = $slugAsli;
        $counter = 1;

        while ($this->sudahDipakai($db, $tabel, $kolomId, $kolomSlug, $slug, $excludeId)) {
            $slug = $slugAsli . '-' . $counter;
            $counter++;

            // Pengaman supaya tidak loop tanpa akhir kalau ada bug lain
            if ($counter > 1000) {
                $slug = $slugAsli . '-' . uniqid();
                break;
            }
        }

        return $slug;
    }

    private function sudahDipakai(
        BaseConnection $db,
        string $tabel,
        string $kolomId,
        string $kolomSlug,
        string $slug,
        ?int $excludeId
    ): bool {
        $builder = $db->table($tabel)->where($kolomSlug, $slug);

        if ($excludeId !== null) {
            $builder->where($kolomId . ' !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }
}