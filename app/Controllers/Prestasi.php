<?php

namespace App\Controllers;

use App\Models\PrestasiModel;

class Prestasi extends BaseController
{
    protected $prestasiModel;

    public function __construct()
    {
        $this->prestasiModel = new PrestasiModel();
    }

    public function index()
    {
        $data['prestasi'] = $this->prestasiModel
            ->where('status', 'Published')
            ->orderBy('tanggal', 'DESC')
            ->findAll();

        return view('prestasi/index', $data);
    }
}