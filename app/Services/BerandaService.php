<?php

namespace App\Services;

use App\Models\AgendaModel;
use App\Models\BeritaModel;
use App\Models\EkstrakurikulerModel;
use App\Models\GaleriModel;
use App\Models\PartnerModel;
use App\Models\PengumumanModel;
use App\Models\PrestasiModel;

/**
 * Semua data yang tampil di halaman beranda publik.
 */
class BerandaService
{
    public const TOP_TAGS = ['LKS', 'PPDB', 'Prestasi', 'Kerjasama', 'OSIS'];

    public function __construct(
        protected BeritaModel $beritaModel,
        protected GaleriModel $galeriModel,
        protected PengumumanModel $pengumumanModel,
        protected AgendaModel $agendaModel,
        protected PrestasiModel $prestasiModel,
        protected PartnerModel $partnerModel,
        protected EkstrakurikulerModel $ekskulModel,
    ) {
    }

    public function data(): array
    {
        return [
            'berita_hero'    => $this->beritaModel->getHero(5),
            'berita_utama'   => $this->beritaModel->getUtama(4),
            'berita_terbaru' => $this->beritaModel->getTerbaru(4),
            'galeri'         => $this->galeriModel->getPublished(null, 4),
            'informasi'      => $this->pengumumanModel->getTerbaru(10),
            'agenda'         => $this->agendaModel->getUpcoming(8),
            'prestasi'       => $this->prestasiModel->getPublished(4),
            'partner'        => $this->partnerModel->getPublished(5),
            'ekskul'         => $this->ekskulModel->getPublished(8),
            'topTags'        => self::TOP_TAGS,
            'tickerBerita'   => $this->beritaModel->getTicker(10),
        ];
    }
}