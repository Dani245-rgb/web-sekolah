<?php

namespace App\Services;

use App\Models\PengaturanModel;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

/**
 * Menyusun data SEO untuk halaman publik: judul, deskripsi, canonical, Open Graph,
 * JSON-LD (data terstruktur untuk Google dan mesin AI), serta isi robots.txt dan llms.txt.
 *
 * Service ini tidak membaca request. Alamat halaman dikirim sebagai parameter ($opsi['path']).
 */
class SeoService
{
    private const BATAS_DESKRIPSI = 160;
    private const BATAS_JUDUL     = 70;
    private const NAMA_BAWAAN     = 'SMK ATAUFIQIYYAH';
    private const FOLDER_SEKOLAH  = 'assets/uploads/sekolah/';
    private const ZONA_WAKTU      = 'Asia/Jakarta';

    private ?array $pengaturan = null;
    private string $baseUrl;

    /**
     * @param string $baseUrl alamat dasar situs (dari base_url())
     */
    public function __construct(
        private PengaturanModel $pengaturanModel,
        string $baseUrl,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }

    // ---------- data sekolah ----------

    public function pengaturan(): array
    {
        return $this->pengaturan ??= ($this->pengaturanModel->getPengaturan() ?: []);
    }

    public function namaSekolah(): string
    {
        $nama = trim((string) ($this->pengaturan()['nama_sekolah'] ?? ''));

        return $nama !== '' ? $nama : self::NAMA_BAWAAN;
    }

    public function url(string $path = ''): string
    {
        return $this->baseUrl . ltrim($path, '/');
    }

    // ---------- meta halaman ----------

    /**
     * @param array{path?: string, gambar?: string, type?: string, noindex?: bool, terbit?: ?string, jsonld?: array} $opsi
     *
     * @return array{title: string, description: string, canonical: string, image: string, type: string, robots: string, siteName: string, terbit: string, jsonld: array}
     */
    public function halaman(string $judul = '', string $deskripsi = '', array $opsi = []): array
    {
        $nama  = $this->namaSekolah();
        $judul = $this->bersihkan($judul);

        if ($judul === '') {
            $title = $nama;
        } else {
            $maks  = max(10, self::BATAS_JUDUL - mb_strlen($nama) - 3);
            $title = mb_strimwidth($judul, 0, $maks, '…') . ' | ' . $nama;
        }

        $deskripsi = $this->ringkas($deskripsi);

        if ($deskripsi === '') {
            $deskripsi = 'Website resmi ' . $nama . '. Berita, pengumuman, jurusan, dan informasi pendaftaran peserta didik baru.';
        }

        $gambar = $this->gambarUrl((string) ($opsi['gambar'] ?? ''));

        return [
            'title'       => $title,
            'description' => $deskripsi,
            'canonical'   => $this->url((string) ($opsi['path'] ?? '')),
            'image'       => $gambar !== '' ? $gambar : $this->logoUrl(),
            'type'        => (string) ($opsi['type'] ?? 'website'),
            'robots'      => empty($opsi['noindex']) ? 'index, follow' : 'noindex, follow',
            'siteName'    => $nama,
            'terbit'      => $this->iso($opsi['terbit'] ?? null),
            'jsonld'      => (array) ($opsi['jsonld'] ?? []),
        ];
    }

    /**
     * Teks polos untuk deskripsi: tanpa tag HTML, spasi dirapikan, dipotong di batas kata.
     */
    public function ringkas(string $teks, int $maks = self::BATAS_DESKRIPSI): string
    {
        $teks = html_entity_decode(strip_tags($teks), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $teks = trim((string) preg_replace('/\s+/u', ' ', $teks));

        if (mb_strlen($teks) <= $maks) {
            return $teks;
        }

        $potong = mb_substr($teks, 0, $maks - 1);
        $spasi  = mb_strrpos($potong, ' ');

        if ($spasi !== false && $spasi > $maks * 0.6) {
            $potong = mb_substr($potong, 0, $spasi);
        }

        return rtrim($potong, " \t,.;:-") . '…';
    }

    // ---------- JSON-LD ----------

    public function jsonLdSekolah(): array
    {
        $p = $this->pengaturan();

        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'EducationalOrganization',
            'name'     => $this->namaSekolah(),
            'url'      => $this->url(),
        ];

        if (($logo = $this->logoUrl()) !== '') {
            $data['logo'] = $logo;
        }

        if (($telepon = trim((string) ($p['telepon'] ?? ''))) !== '') {
            $data['telephone'] = $telepon;
        }

        if (($email = trim((string) ($p['email'] ?? ''))) !== '') {
            $data['email'] = $email;
        }

        if (($alamat = trim((string) ($p['alamat'] ?? ''))) !== '') {
            $data['address'] = [
                '@type'          => 'PostalAddress',
                'streetAddress'  => $alamat,
                'addressCountry' => 'ID',
            ];
        }

        return $data;
    }

    /**
     * @param array $berita baris tabel berita (judul, ringkasan, konten, tanggal_publish, updated_at)
     */
    public function jsonLdArtikel(array $berita, string $path, string $gambar = ''): array
    {
        $nama = $this->namaSekolah();

        $data = [
            '@context'         => 'https://schema.org',
            '@type'            => 'NewsArticle',
            'headline'         => mb_strimwidth($this->bersihkan((string) ($berita['judul'] ?? '')), 0, 110, '…'),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $this->url($path)],
            'author'           => ['@type' => 'Organization', 'name' => $nama],
            'publisher'        => $this->penerbit(),
        ];

        if ($gambar !== '') {
            $data['image'] = [$gambar];
        }

        $terbit = $this->iso($berita['tanggal_publish'] ?? null);

        if ($terbit !== '') {
            $data['datePublished'] = $terbit;
        }

        $diubah = $this->iso($berita['updated_at'] ?? null) ?: $terbit;

        if ($diubah !== '') {
            $data['dateModified'] = $diubah;
        }

        $deskripsi = $this->ringkas((string) (($berita['ringkasan'] ?? '') ?: ($berita['konten'] ?? '')));

        if ($deskripsi !== '') {
            $data['description'] = $deskripsi;
        }

        return $data;
    }

    /**
     * @param list<array{nama: string, path?: string}> $butir urutan dari Beranda sampai halaman ini
     */
    public function jsonLdBreadcrumb(array $butir): array
    {
        $item = [];

        foreach (array_values($butir) as $i => $b) {
            $item[] = [
                '@type'    => 'ListItem',
                'position' => $i + 1,
                'name'     => (string) $b['nama'],
                'item'     => $this->url((string) ($b['path'] ?? '')),
            ];
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $item,
        ];
    }

    /**
     * JSON untuk ditaruh di dalam <script type="application/ld+json">.
     * Karakter < dan > di-escape supaya isi data tidak bisa menutup tag script.
     */
    public function jsonLdKeScript(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);

        return $json === false ? '' : $json;
    }

    // ---------- berkas teks untuk crawler ----------

    public function robotsTxt(): string
    {
        return implode("\n", [
            '# Semua crawler, termasuk crawler mesin pencari AI, boleh membaca halaman publik.',
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /login',
            'Disallow: /logout',
            'Disallow: /profil/gantipassword',
            '',
            'Sitemap: ' . $this->url('sitemap.xml'),
            '',
        ]);
    }

    /**
     * Ringkasan situs untuk mesin AI (format llms.txt).
     */
    public function llmsTxt(): string
    {
        $p    = $this->pengaturan();
        $nama = $this->namaSekolah();

        $baris = [
            '# ' . $nama,
            '',
            '> Website resmi ' . $nama . '. Berisi berita, pengumuman, informasi jurusan, dan pendaftaran peserta didik baru.',
            '',
            '## Informasi utama',
            '',
            '- [Beranda](' . $this->url() . '): Halaman utama sekolah',
            '- [Berita](' . $this->url('berita') . '): Berita dan kegiatan terbaru',
            '- [Pengumuman](' . $this->url('pengumuman') . '): Pengumuman resmi sekolah',
            '- [Jurusan](' . $this->url('akademik/jurusan') . '): Daftar jurusan',
            '- [Guru dan Staf](' . $this->url('akademik/guru') . '): Tenaga pendidik',
            '- [Kalender Akademik](' . $this->url('akademik/kalender') . '): Jadwal kegiatan akademik',
            '- [PPDB](' . $this->url('ppdb') . '): Informasi dan formulir pendaftaran peserta didik baru',
            '- [Profil Sekolah](' . $this->url('profil/sejarah') . '): Sejarah, visi dan misi, kepala sekolah',
            '- [Kontak](' . $this->url('kontak') . '): Hubungi sekolah',
        ];

        $kontak = [];

        foreach (['alamat' => 'Alamat', 'telepon' => 'Telepon', 'email' => 'Email'] as $kolom => $label) {
            $nilai = trim((string) ($p[$kolom] ?? ''));

            if ($nilai !== '') {
                $kontak[] = '- ' . $label . ': ' . $nilai;
            }
        }

        if ($kontak !== []) {
            $baris[] = '';
            $baris[] = '## Kontak';
            $baris[] = '';
            $baris   = array_merge($baris, $kontak);
        }

        $baris[] = '';

        return implode("\n", $baris);
    }

    // ---------- bantu ----------

    private function logoUrl(): string
    {
        $logo = trim((string) ($this->pengaturan()['logo'] ?? ''));

        return $logo !== '' ? $this->url(self::FOLDER_SEKOLAH . $logo) : '';
    }

    private function gambarUrl(string $gambar): string
    {
        if ($gambar === '') {
            return '';
        }

        return preg_match('#^https?://#i', $gambar) ? $gambar : $this->url($gambar);
    }

    private function penerbit(): array
    {
        $data = ['@type' => 'Organization', 'name' => $this->namaSekolah()];

        if (($logo = $this->logoUrl()) !== '') {
            $data['logo'] = ['@type' => 'ImageObject', 'url' => $logo];
        }

        return $data;
    }

    private function bersihkan(string $teks): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $teks));
    }

    /**
     * Waktu dari database (zona WIB) jadi format ISO 8601, atau '' kalau kosong/tidak valid.
     */
    private function iso(?string $waktu): string
    {
        $waktu = trim((string) $waktu);

        if ($waktu === '' || str_starts_with($waktu, '0000')) {
            return '';
        }

        try {
            return (new DateTimeImmutable($waktu, new DateTimeZone(self::ZONA_WAKTU)))->format('c');
        } catch (Throwable) {
            return '';
        }
    }
}
