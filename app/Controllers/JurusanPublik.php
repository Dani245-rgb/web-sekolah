<?php

namespace App\Controllers;

use App\Models\JurusanModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class JurusanPublik extends BaseController
{
    protected JurusanModel $jurusanModel;

    public function __construct()
    {
        $this->jurusanModel = new JurusanModel();
    }

    public function index()
    {
        $data['jurusanList'] = $this->jurusanModel->getSemuaUrut();

        return view('jurusan_publik/index', $data);
    }

    public function detail(string $slug)
    {
        $item = $this->jurusanModel->getBySlug($slug);

        if (!$item) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['item'] = $item;

        return view('jurusan_publik/detail', $data);
    }
}