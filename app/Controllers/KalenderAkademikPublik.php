<?php

namespace App\Controllers;

use App\Models\KalenderAkademikModel;

class KalenderAkademikPublik extends BaseController
{
    protected KalenderAkademikModel $kalenderModel;

    public function __construct()
    {
        $this->kalenderModel = new KalenderAkademikModel();
    }

    public function index()
    {
        $data['kalender'] = $this->kalenderModel->getPublished();

        return view('kalender_akademik_publik/index', $data);
    }
}