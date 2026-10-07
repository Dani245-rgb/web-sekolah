<?php

namespace App\Services;

use App\Models\NotifikasiModel;
use InvalidArgumentException;
use RuntimeException;

/**
 * Logika notifikasi admin: daftar, ringkasan untuk polling topbar, tandai dibaca,
 * dan penentuan apakah link notifikasi aman untuk di-redirect.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * RuntimeException         = kegagalan sistem (controller yang log + tampilkan pesan umum).
 */
class NotifikasiService
{
    public const TIDAK_DITEMUKAN = 'Notifikasi tidak ditemukan.';

    /**
     * @param string $baseUrl alamat dasar situs ini (dari base_url()), dipakai untuk menilai link internal
     */
    public function __construct(
        private NotifikasiModel $notifikasiModel,
        private string $baseUrl,
    ) {
    }

    public function daftar(int $userId, int $limit = 50): array
    {
        return $this->notifikasiModel->getForUser($userId, $limit);
    }

    /**
     * Data untuk polling topbar: jumlah belum dibaca + 5 notifikasi terbaru.
     */
    public function ringkasan(int $userId): array
    {
        return [
            'unread_count' => $this->notifikasiModel->countUnread($userId),
            'terbaru'      => $this->notifikasiModel->getForUser($userId, 5),
        ];
    }

    /**
     * Tandai satu notifikasi dibaca. Return link tujuan kalau ada dan aman (internal),
     * atau null kalau tidak ada link / link ditolak.
     */
    public function baca(int $idNotif, int $userId): ?string
    {
        $notif = $this->notifikasiModel->cariUntukUser($idNotif, $userId);

        if (!$notif) {
            throw new InvalidArgumentException(self::TIDAK_DITEMUKAN);
        }

        $this->notifikasiModel->tandaiDibaca($idNotif);

        $link = (string) ($notif['link'] ?? '');

        if ($link !== '' && $this->linkInternal($link)) {
            return $link;
        }

        return null;
    }

    public function bacaSemua(int $userId): void
    {
        if ($this->notifikasiModel->tandaiSemuaDibaca($userId) === false) {
            throw new RuntimeException('Gagal menandai semua notifikasi dibaca (user ' . $userId . ').');
        }
    }

    /**
     * Link boleh relatif (admin/kontak/...) atau alamat situs ini sendiri.
     * Alamat luar (https://situs-lain.com, //situs-lain.com, javascript:...) ditolak.
     */
    private function linkInternal(string $link): bool
    {
        if ($this->baseUrl !== '' && str_starts_with($link, $this->baseUrl)) {
            return true;
        }

        return !preg_match('#^([a-z][a-z0-9+.\-]*:|//)#i', $link);
    }
}