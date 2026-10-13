<?php

namespace Tests\Unit;

use App\Models\BeritaModel;
use App\Models\PengumumanModel;
use App\Services\SitemapService;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class SitemapServiceTest extends CIUnitTestCase
{
    private BeritaModel&MockObject $berita;
    private PengumumanModel&MockObject $pengumuman;
    private SitemapService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->berita     = $this->createMock(BeritaModel::class);
        $this->pengumuman = $this->createMock(PengumumanModel::class);

        $this->berita->method('untukSitemap')->willReturn([
            ['slug' => 'a-b', 'tanggal_publish' => '2026-10-01', 'updated_at' => '2026-10-05 10:00:00'],
            ['slug' => '', 'tanggal_publish' => '2026-10-01', 'updated_at' => null],
            ['slug' => 'c', 'tanggal_publish' => '2026-09-30', 'updated_at' => '0000-00-00 00:00:00'],
        ]);
        $this->pengumuman->method('untukSitemap')->willReturn([
            ['slug' => 'p&q', 'tanggal_publish' => '2026-10-02', 'updated_at' => null],
        ]);

        $this->service = new SitemapService($this->berita, $this->pengumuman, 'http://x.test');
    }

    public function testMemuatHalamanTetap(): void
    {
        $locs = array_column($this->service->urls(), 'loc');

        $this->assertContains('http://x.test/', $locs);
        $this->assertContains('http://x.test/berita', $locs);
        $this->assertContains('http://x.test/ppdb', $locs);
        $this->assertContains('http://x.test/profil/visi-misi', $locs);
    }

    public function testMemuatBeritaDanPengumumanDenganSlug(): void
    {
        $locs = array_column($this->service->urls(), 'loc');

        $this->assertContains('http://x.test/berita/a-b', $locs);
        $this->assertContains('http://x.test/berita/c', $locs);
        $this->assertContains('http://x.test/pengumuman/p%26q', $locs);
    }

    public function testSlugKosongDilewati(): void
    {
        $locs = array_column($this->service->urls(), 'loc');

        $this->assertNotContains('http://x.test/berita/', $locs);
    }

    public function testLastmodDariUpdatedAtLaluTanggalPublish(): void
    {
        $map = array_column($this->service->urls(), 'lastmod', 'loc');

        $this->assertSame('2026-10-05', $map['http://x.test/berita/a-b']);
        $this->assertSame('2026-09-30', $map['http://x.test/berita/c'], 'updated_at nol harus diabaikan');
        $this->assertSame('2026-10-02', $map['http://x.test/pengumuman/p%26q']);
        $this->assertNull($map['http://x.test/berita']);
    }

    public function testXmlValidDanJumlahUrlSama(): void
    {
        $xml = simplexml_load_string($this->service->xml());

        $this->assertNotFalse($xml);
        $this->assertCount(count($this->service->urls()), $xml->url);
    }
}
