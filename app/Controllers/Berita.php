<?php

namespace App\Controllers;

use App\Models\BeritaModel;
use App\Models\PengumumanModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Berita extends BaseController
{
    protected BeritaModel $beritaModel;
    protected PengumumanModel $pengumumanModel;

    public function __construct()
    {
        $this->beritaModel     = new BeritaModel();
        $this->pengumumanModel = new PengumumanModel();
    }

    public function index()
    {
        return view('berita_index', [
            'beritaList' => $this->beritaModel->getPublishedPaginated(9),
            'pager'      => $this->beritaModel->pager,
            'informasi'  => $this->getInformasiSidebar(),
        ]);
    }

    public function detail($slug)
    {
        $berita = $this->beritaModel->getBySlug($slug);

        if (!$berita) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('berita_detail', [
            'berita'    => $berita,
            'terkait'   => $this->beritaModel->getTerkait($berita, 3),
            'informasi' => $this->getInformasiSidebar(),
        ]);
    }

    /**
     * Data "Informasi Sekolah" yang tampil di layouts/sidebar.php.
     * Dipakai bersama oleh index() dan detail() (dan halaman publik lain yang pakai sidebar sama).
     */
    protected function getInformasiSidebar(int $limit = 5): array
    {
        return $this->pengumumanModel->getTerbaru($limit);
    }
}