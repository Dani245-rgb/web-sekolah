<?php

namespace App\Controllers;

use App\Models\BeritaModel;
use App\Models\GaleriModel;
use App\Models\PengumumanModel;
use App\Models\AgendaModel;
use App\Models\PrestasiModel;
use App\Models\PartnerModel;
use App\Models\EkstrakurikulerModel;

class Home extends BaseController
{
    public function index(): string
    {
        helper('text');

        $beritaModel      = new BeritaModel();
        $galeriModel      = new GaleriModel();
        $pengumumanModel  = new PengumumanModel();
        $agendaModel      = new AgendaModel();
        $prestasiModel    = new PrestasiModel();
        $partnerModel     = new PartnerModel();
        $ekskulModel      = new EkstrakurikulerModel();

        return view('home', [
            'berita_hero'    => $beritaModel->getHero(5),
            'berita_utama'   => $beritaModel->getUtama(4),
            'berita_terbaru' => $beritaModel->getTerbaru(4),
            'galeri'         => $galeriModel->getPublished(null, 4),
            'informasi'      => $pengumumanModel->getPublished(10),
            'agenda'         => $agendaModel->getUpcoming(8),
            'prestasi'       => $prestasiModel->getPublished(4),
            'partner'        => $partnerModel->getPublished(5),
            'ekskul'         => $ekskulModel->getPublished(8),
            'topTags'        => ['LKS', 'PPDB', 'Prestasi', 'Kerjasama', 'OSIS'],
            'tickerBerita'   => $beritaModel->getTicker(10),
        ]);
    }
}
