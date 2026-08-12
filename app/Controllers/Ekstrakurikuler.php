<?php

namespace App\Controllers;

use App\Models\EkstrakurikulerModel;

class Ekstrakurikuler extends BaseController
{
    public function index()
    {
        $ekskulModel = new EkstrakurikulerModel();

        $data['ekskul'] = $ekskulModel->where('status', 'Published')
            ->orderBy('nama', 'ASC')
            ->findAll();

        return view('ekstrakurikuler/index', $data);
    }
}