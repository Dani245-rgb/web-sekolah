<?php

namespace App\Controllers;

use App\Models\PengumumanModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Pengumuman extends BaseController
{
    protected PengumumanModel $pengumumanModel;

    public function __construct()
    {
        $this->pengumumanModel = new PengumumanModel();
    }

    public function index()
    {
        $data['pengumuman'] = $this->pengumumanModel->getPublished(10);
        $data['pager']      = $this->pengumumanModel->pager;

        return view('pengumuman/index', $data);
    }

    public function detail($slug)
    {
        $pengumuman = $this->pengumumanModel->getBySlug($slug);

        if (!$pengumuman) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['pengumuman'] = $pengumuman;

        return view('pengumuman/detail', $data);
    }
}