<?php

namespace App\Controllers;

use App\Models\JurusanModel;

class JurusanPublik extends BaseController
{
    public function index()
    {
        $jurusanModel = new JurusanModel();

        $data['jurusanList'] = $jurusanModel
            ->orderBy('nama_jurusan', 'ASC')
            ->findAll();

        return view('jurusan_publik/index', $data);
    }

    public function detail(string $slug)
    {
        $jurusanModel = new JurusanModel();

        $data['item'] = $jurusanModel->getBySlug($slug);

        return view('jurusan_publik/detail', $data);
    }
}