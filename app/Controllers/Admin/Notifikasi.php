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
        $this->model->tandaiDibaca($idNotif);
        $notif = $this->model->find($idNotif);

        if ($notif && !empty($notif['link'])) {
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