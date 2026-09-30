<?php

namespace App\Controllers;

use App\Models\GuruModel;

class GuruPublik extends BaseController
{
    protected GuruModel $guruModel;

    public function __construct()
    {
        $this->guruModel = new GuruModel();
    }

    public function index()
    {
        $data['guru'] = $this->guruModel->getAktif();

        return view('guru_publik/index', $data);
    }
}