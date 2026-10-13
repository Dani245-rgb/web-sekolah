<?php

namespace App\Controllers;

use App\Models\BeritaModel;
use App\Models\PengumumanModel;
use App\Services\SeoService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Berita extends BaseController
{
    protected BeritaModel $beritaModel;
    protected PengumumanModel $pengumumanModel;
    protected SeoService $seo;

    public function __construct()
    {
        $this->beritaModel     = new BeritaModel();
        $this->pengumumanModel = new PengumumanModel();
        $this->seo             = service('seoService');
    }

    public function index()
    {
        return view('berita_index', [
            'beritaList' => $this->beritaModel->getPublishedPaginated(9),
            'pager'      => $this->beritaModel->pager,
            'informasi'  => $this->getInformasiSidebar(),
            'seo'        => $this->seo->halaman(
                'Berita',
                'Kumpulan berita, prestasi, dan kegiatan terbaru ' . $this->seo->namaSekolah() . '.',
                [
                    'path'   => 'berita',
                    'jsonld' => [
                        $this->seo->jsonLdBreadcrumb([
                            ['nama' => 'Beranda', 'path' => ''],
                            ['nama' => 'Berita', 'path' => 'berita'],
                        ]),
                    ],
                ]
            ),
        ]);
    }

    public function detail($slug)
    {
        $berita = $this->beritaModel->getBySlug($slug);

        if (!$berita) {
            throw PageNotFoundException::forPageNotFound();
        }

        $path   = 'berita/' . $berita['slug'];
        $gambar = !empty($berita['gambar']) ? $this->seo->url('assets/images/berita/' . $berita['gambar']) : '';

        return view('berita_detail', [
            'berita'    => $berita,
            'terkait'   => $this->beritaModel->getTerkait($berita, 3),
            'informasi' => $this->getInformasiSidebar(),
            'seo'       => $this->seo->halaman(
                (string) $berita['judul'],
                (string) (($berita['ringkasan'] ?? '') ?: $berita['konten']),
                [
                    'path'   => $path,
                    'gambar' => $gambar,
                    'type'   => 'article',
                    'terbit' => $berita['tanggal_publish'] ?? null,
                    'jsonld' => [
                        $this->seo->jsonLdArtikel($berita, $path, $gambar),
                        $this->seo->jsonLdBreadcrumb([
                            ['nama' => 'Beranda', 'path' => ''],
                            ['nama' => 'Berita', 'path' => 'berita'],
                            ['nama' => (string) $berita['judul'], 'path' => $path],
                        ]),
                    ],
                ]
            ),
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
