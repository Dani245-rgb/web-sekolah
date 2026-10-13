<?php

namespace App\Services;

use App\Models\BeritaModel;
use App\Models\PengumumanModel;

/**
 * Menyusun sitemap.xml: halaman tetap + berita dan pengumuman yang sudah terbit.
 */
class SitemapService
{
    private const HALAMAN_TETAP = [
        '',
        'berita',
        'pengumuman',
        'prestasi',
        'agenda',
        'galeri',
        'ekstrakurikuler',
        'partner',
        'ppdb',
        'kontak',
        'akademik/guru',
        'akademik/kalender',
        'akademik/jurusan',
        'profil/sejarah',
        'profil/visi-misi',
        'profil/struktur',
        'profil/kepala-sekolah',
        'bk/kesehatan-mental',
        'bk/karier',
        'bk/tes-minat',
        'unduhan',
    ];

    private string $baseUrl;

    /**
     * @param string $baseUrl alamat dasar situs (dari base_url())
     */
    public function __construct(
        private BeritaModel $beritaModel,
        private PengumumanModel $pengumumanModel,
        string $baseUrl,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    /**
     * @return list<array{loc: string, lastmod: ?string}>
     */
    public function urls(): array
    {
        $urls = [];

        foreach (self::HALAMAN_TETAP as $path) {
            $urls[] = ['loc' => $this->baseUrl . $path, 'lastmod' => null];
        }

        foreach ($this->beritaModel->untukSitemap() as $baris) {
            $this->tambah($urls, 'berita/', $baris);
        }

        foreach ($this->pengumumanModel->untukSitemap() as $baris) {
            $this->tambah($urls, 'pengumuman/', $baris);
        }

        return $urls;
    }

    public function xml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($this->urls() as $url) {
            $xml .= "  <url>\n    <loc>" . $this->xmlAman($url['loc']) . "</loc>\n";

            if ($url['lastmod'] !== null) {
                $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            }

            $xml .= "  </url>\n";
        }

        return $xml . '</urlset>' . "\n";
    }

    private function tambah(array &$urls, string $awalan, array $baris): void
    {
        $slug = trim((string) ($baris['slug'] ?? ''));

        if ($slug === '') {
            return;
        }

        $urls[] = [
            'loc'     => $this->baseUrl . $awalan . rawurlencode($slug),
            'lastmod' => $this->lastmod($baris),
        ];
    }

    /**
     * Tanggal (Y-m-d) dari updated_at, atau tanggal_publish kalau updated_at kosong.
     */
    private function lastmod(array $baris): ?string
    {
        foreach (['updated_at', 'tanggal_publish'] as $kolom) {
            $nilai = (string) ($baris[$kolom] ?? '');

            if (preg_match('/^(?!0000)\d{4}-\d{2}-\d{2}/', $nilai, $cocok)) {
                return $cocok[0];
            }
        }

        return null;
    }

    private function xmlAman(string $teks): string
    {
        return htmlspecialchars($teks, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
