<?php

namespace App\Controllers;

use App\Models\GaleriModel;

class Galeri extends BaseController
{
    protected GaleriModel $galeriModel;

    public function __construct()
    {
        $this->galeriModel = new GaleriModel();
    }

    public function index()
    {
        // Hanya kategori yang dikenal yang dipakai, selain itu tampil semua
        $kategori = $this->request->getGet('kategori');
        $kategori = in_array($kategori, GaleriModel::KATEGORI, true) ? $kategori : null;

        $data['galeri']   = $this->galeriModel->getPublished($kategori, 100, 0);
        $data['kategori'] = $kategori;

        return view('galeri/index', $data);
    }
}