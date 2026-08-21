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
        $userId = session()->get('id_user');
        $data['notifikasi'] = $this->model->getForUser($userId, 50);

        return view('admin/notifikasi/index', $data);
    }

    /**
     * Dipanggil AJAX tiap beberapa detik dari topbar.
     */
    public function poll()
    {
        $userId = session()->get('id_user');

        return $this->response->setJSON([
            'unread_count' => $this->model->countUnread($userId),
            'terbaru'      => $this->model->getForUser($userId, 5),
        ]);
    }

    public function baca($idNotif)
    {
        $userId = session()->get('id_user');
        $notif  = $this->model->find($idNotif);

        // Boleh ditandai kalau: milik sendiri, ATAU broadcast (user_id null, buat semua Admin)
        $bolehAkses = $notif && ($notif['user_id'] === null || (int) $notif['user_id'] === (int) $userId);

        if (!$bolehAkses) {
            return redirect()->to('/admin/notifikasi')->with('error', 'Notifikasi tidak ditemukan.');
        }

        $this->model->tandaiDibaca($idNotif);

        if (!empty($notif['link'])) {
            return redirect()->to($notif['link']);
        }

        return redirect()->to('/admin/notifikasi');
    }
    public function bacaSemua()
    {
        $this->model->tandaiSemuaDibaca(session()->get('id_user'));
        return redirect()->back();
    }
}
