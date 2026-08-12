<?php

namespace App\Controllers;

use App\Models\PengumumanModel;

class Pengumuman extends BaseController
{
    protected $pengumumanModel;

    public function __construct()
    {
        $this->pengumumanModel = new PengumumanModel();
    }

    public function index()
    {
        $perPage = 10;
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $offset  = ($page - 1) * $perPage;

        $data['pengumuman'] = $this->pengumumanModel->getPublished($perPage, $offset);
        $data['pager']      = $this->pengumumanModel->pager;

        return view('pengumuman/index', $data);
    }

    public function detail($slug)
    {
        $pengumuman = $this->pengumumanModel->getBySlug($slug);

        if (!$pengumuman) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['pengumuman'] = $pengumuman;

        return view('pengumuman/detail', $data);
    }
}