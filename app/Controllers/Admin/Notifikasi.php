<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NotifikasiModel;

class Notifikasi extends BaseController
{
    protected NotifikasiModel $model;

    public function __construct()
    {
        $this->model = new NotifikasiModel();
    }

    public function index()
    {
        $data['notifikasi'] = $this->model->getForUser($this->idUser(), 50);

        return view('admin/notifikasi/index', $data);
    }

    /**
     * Dipanggil AJAX tiap beberapa detik dari topbar.
     */
    public function poll()
    {
        $userId = $this->idUser();

        return $this->response->setJSON([
            'unread_count' => $this->model->countUnread($userId),
            'terbaru'      => $this->model->getForUser($userId, 5),
        ]);
    }

    public function baca($idNotif)
    {
        $notif = $this->model->cariUntukUser((int) $idNotif, $this->idUser());

        if (!$notif) {
            return redirect()->to('/admin/notifikasi')->with('error', 'Notifikasi tidak ditemukan.');
        }

        $this->model->tandaiDibaca((int) $idNotif);

        if (!empty($notif['link']) && $this->linkInternal($notif['link'])) {
            return redirect()->to($notif['link']);
        }

        return redirect()->to('/admin/notifikasi');
    }

    public function bacaSemua()
    {
        $this->model->tandaiSemuaDibaca($this->idUser());
        return redirect()->back();
    }

    private function idUser(): int
    {
        return (int) session()->get('id_user');
    }

    /**
     * Link boleh relatif (admin/kontak/...) atau alamat situs ini sendiri.
     * Alamat luar (https://situs-lain.com, //situs-lain.com, javascript:...) ditolak.
     */
    private function linkInternal(string $link): bool
    {
        if (str_starts_with($link, base_url())) {
            return true;
        }

        return !preg_match('#^([a-z][a-z0-9+.\-]*:|//)#i', $link);
    }
}