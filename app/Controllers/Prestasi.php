<?php

namespace App\Controllers;

use App\Models\PrestasiModel;

class Prestasi extends BaseController
{
    protected PrestasiModel $prestasiModel;

    public function __construct()
    {
        $this->prestasiModel = new PrestasiModel();
    }

    public function index()
    {
        $data['prestasi'] = $this->prestasiModel->getSemuaPublished();

        return view('prestasi/index', $data);
    }
}