<?php

namespace App\Controllers;

use App\Models\BeritaModel;

class Home extends BaseController
{
    public function index(): string
    {
        $beritaModel = new BeritaModel();

        return view('home', [
            'berita_hero'    => $beritaModel->getHero(),
            'berita_utama'   => $beritaModel->getUtama(3),
            'berita_terbaru' => $beritaModel->getTerbaru(4),
        ]);
    }
}
