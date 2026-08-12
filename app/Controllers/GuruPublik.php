<?php

namespace App\Controllers;

use App\Models\GuruModel;

class GuruPublik extends BaseController
{
    public function index()
    {
        $guruModel = new GuruModel();

        $data['guru'] = $guruModel->where('status', 'Aktif')
            ->orderBy('nama', 'ASC')
            ->findAll();

        return view('guru_publik/index', $data);
    }
}