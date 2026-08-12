<?php

namespace App\Controllers;

use App\Models\GaleriModel;

class Galeri extends BaseController
{
    protected $galeriModel;

    public function __construct()
    {
        $this->galeriModel = new GaleriModel();
    }

    public function index()
    {
        $kategori = $this->request->getGet('kategori');

        $data['galeri']   = $this->galeriModel->getPublished($kategori, 100, 0);
        $data['kategori'] = $kategori;

        return view('galeri/index', $data);
    }
}