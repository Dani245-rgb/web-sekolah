<?php

namespace App\Controllers;

use App\Models\KalenderAkademikModel;

class KalenderAkademikPublik extends BaseController
{
    public function index()
    {
        $kalenderModel = new KalenderAkademikModel();

        $data['kalender'] = $kalenderModel->getPublished();

        return view('kalender_akademik_publik/index', $data);
    }
}