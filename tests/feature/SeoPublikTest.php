<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;
use Config\Services;

/**
 * Feature test SEO publik: sitemap.xml, robots.txt, llms.txt, dan meta/JSON-LD
 * di halaman berita. Memakai database TES (web_sekolah_test).
 *
 * @internal
 */
final class SeoPublikTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $DBGroup   = 'tests';
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();

        // View bawaan CI menyimpan data antar render dalam satu proses (saveData). Di produksi
        // tiap request proses baru, tapi di tes beberapa request jalan berurutan, jadi direset.
        Services::resetSingle('renderer');

        $this->assertSame('testing', ENVIRONMENT);
        $this->assertSame('tests', config('Database')->defaultGroup);
        $this->assertSame('web_sekolah_test', Database::connect()->getDatabase());
        $this->assertSame('web_sekolah_test', $this->db->getDatabase());

        $this->db->query('SET FOREIGN_KEY_CHECKS=0');

        foreach (['berita', 'pengumuman', 'pengaturan_sekolah'] as $tabel) {
            $this->db->table($tabel)->emptyTable();
        }

        $this->db->table('pengaturan_sekolah')->insert([
            'nama_sekolah' => 'SMK Tes SEO',
            'alamat'       => 'Jl. Mawar 1',
            'telepon'      => '021-123',
            'email'        => 'a@b.id',
            'logo'         => 'logo.png',
        ]);

        $this->db->table('berita')->insertBatch([
            $this->baris('Berita Terbit Pertama', 'berita-terbit-pertama', 'Published'),
            $this->baris('Berita Masih Draft', 'berita-masih-draft', 'Draft'),
        ]);

        $this->db->table('pengumuman')->insertBatch([
            [
                'judul' => 'Pengumuman Libur', 'slug' => 'pengumuman-libur', 'isi' => 'Libur.',
                'tanggal_publish' => '2026-01-01', 'status' => 'Published',
                'created_at' => '2026-01-01 08:00:00', 'updated_at' => '2026-01-01 08:00:00',
            ],
            [
                'judul' => 'Pengumuman Draft', 'slug' => 'pengumuman-draft', 'isi' => 'Draft.',
                'tanggal_publish' => '2026-01-01', 'status' => 'Draft',
                'created_at' => '2026-01-01 08:00:00', 'updated_at' => '2026-01-01 08:00:00',
            ],
        ]);

        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    private function baris(string $judul, string $slug, string $status): array
    {
        return [
            'judul'           => $judul,
            'slug'            => $slug,
            'kategori'        => 'Umum',
            'gambar'          => '',
            'ringkasan'       => 'Ringkasan berita untuk deskripsi meta.',
            'konten'          => 'Isi lengkap berita.',
            'status'          => $status,
            'posisi'          => 'biasa',
            'tanggal_publish' => '2026-10-09',
            'id_user'         => 1,
            'created_at'      => '2026-10-09 08:00:00',
            'updated_at'      => '2026-10-09 08:00:00',
        ];
    }

    // ---------- sitemap / robots / llms ----------

    public function testSitemapMemuatYangTerbitSajaDanXmlValid(): void
    {
        $result = $this->get('sitemap.xml');

        $result->assertOK();
        $this->assertStringContainsString('application/xml', $result->response()->getHeaderLine('Content-Type'));

        $body = $result->response()->getBody();
        libxml_use_internal_errors(true);
        $xml    = simplexml_load_string($body);
        $galat  = implode(' | ', array_map(static fn ($e) => trim($e->message), libxml_get_errors()));
        libxml_clear_errors();
        $this->assertNotFalse($xml, 'sitemap harus XML valid. Galat: ' . $galat . ' || Awal isi: ' . json_encode(substr($body, 0, 300)));
        $this->assertStringContainsString(base_url('berita/berita-terbit-pertama'), $body);
        $this->assertStringContainsString(base_url('pengumuman/pengumuman-libur'), $body);
        $this->assertStringNotContainsString('berita-masih-draft', $body);
        $this->assertStringNotContainsString('pengumuman-draft', $body);
    }

    public function testRobotsTxt(): void
    {
        $result = $this->get('robots.txt');

        $result->assertOK();
        $this->assertStringContainsString('text/plain', $result->response()->getHeaderLine('Content-Type'));
        $result->assertSee('Disallow: /admin/');
        $result->assertSee('Sitemap: ' . base_url('sitemap.xml'));
    }

    public function testLlmsTxt(): void
    {
        $result = $this->get('llms.txt');

        $result->assertOK();
        $result->assertSee('# SMK Tes SEO');
        $result->assertSee('Jl. Mawar 1');
    }

    // ---------- meta di halaman publik ----------

    public function testDetailBeritaMemuatMetaDanJsonLd(): void
    {
        $result = $this->get('berita/berita-terbit-pertama');

        $result->assertOK();
        $html = $result->getBody();

        $this->assertStringContainsString('<html lang="id">', $html);
        $this->assertStringContainsString('<title>Berita Terbit Pertama | SMK Tes SEO</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="Ringkasan berita untuk deskripsi meta.">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . base_url('berita/berita-terbit-pertama') . '">', $html);
        $this->assertStringContainsString('<meta property="og:type" content="article">', $html);
        $this->assertStringContainsString('"@type":"NewsArticle"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
    }

    public function testBeritaDraftTidakBisaDibuka(): void
    {
        try {
            $result = $this->get('berita/berita-masih-draft');
            $result->assertStatus(404);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }

    public function testHalamanLainTetapPunyaMetaBawaan(): void
    {
        $result = $this->get('kontak');

        $result->assertOK();
        $html = $result->getBody();

        $this->assertStringContainsString('<html lang="id">', $html);
        $this->assertStringContainsString('<meta name="description"', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . base_url('kontak') . '">', $html);
        $this->assertStringContainsString('<meta property="og:site_name" content="SMK Tes SEO">', $html);
    }
}
