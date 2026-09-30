<?php

namespace App\Controllers;

use App\Models\EkstrakurikulerModel;

class Ekstrakurikuler extends BaseController
{
    protected EkstrakurikulerModel $ekskulModel;

    public function __construct()
    {
        $this->ekskulModel = new EkstrakurikulerModel();
    }

    public function index()
    {
        $data['ekskul'] = $this->ekskulModel->getSemuaPublished();

        return view('ekstrakurikuler/index', $data);
    }
}