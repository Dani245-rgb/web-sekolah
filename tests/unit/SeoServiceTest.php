<?php

namespace Tests\Unit;

use App\Models\PengaturanModel;
use App\Services\SeoService;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class SeoServiceTest extends CIUnitTestCase
{
    private PengaturanModel&MockObject $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = $this->createMock(PengaturanModel::class);
    }

    private function service(?array $pengaturan = null, string $base = 'http://x.test'): SeoService
    {
        $this->model->method('getPengaturan')->willReturn($pengaturan ?? [
            'nama_sekolah' => 'SMK Tes',
            'alamat'       => 'Jl. Mawar 1',
            'telepon'      => '021-123',
            'email'        => 'a@b.id',
            'logo'         => 'logo.png',
        ]);

        return new SeoService($this->model, $base);
    }

    // ---------- halaman() ----------

    public function testJudulDigabungDenganNamaSekolah(): void
    {
        $h = $this->service()->halaman('Libur Semester');

        $this->assertSame('Libur Semester | SMK Tes', $h['title']);
        $this->assertSame('SMK Tes', $h['siteName']);
    }

    public function testTanpaJudulHanyaNamaSekolah(): void
    {
        $this->assertSame('SMK Tes', $this->service()->halaman()['title']);
    }

    public function testJudulPanjangDipotongTidakLebih70Karakter(): void
    {
        $h = $this->service()->halaman(str_repeat('Judul panjang sekali ', 10));

        $this->assertLessThanOrEqual(70, mb_strlen($h['title']));
        $this->assertStringEndsWith(' | SMK Tes', $h['title']);
        $this->assertStringContainsString('…', $h['title']);
    }

    public function testDeskripsiDibersihkanDariTagDanEntitas(): void
    {
        $h = $this->service()->halaman('x', '<p>Libur &amp; cuti   dimulai.</p>');

        $this->assertSame('Libur & cuti dimulai.', $h['description']);
    }

    public function testDeskripsiKosongMemakaiBawaan(): void
    {
        $h = $this->service()->halaman('x');

        $this->assertStringContainsString('SMK Tes', $h['description']);
        $this->assertNotSame('', $h['description']);
    }

    public function testCanonicalMemakaiBaseUrlDanPath(): void
    {
        $h = $this->service()->halaman('x', '', ['path' => '/berita/libur']);

        $this->assertSame('http://x.test/berita/libur', $h['canonical']);
        $this->assertSame('http://x.test/', $this->service()->halaman()['canonical']);
    }

    public function testGambarBawaanAdalahLogo(): void
    {
        $this->assertSame(
            'http://x.test/assets/uploads/sekolah/logo.png',
            $this->service()->halaman('x')['image'],
        );
    }

    public function testGambarKhususDipakaiBilaAda(): void
    {
        $h = $this->service()->halaman('x', '', ['gambar' => 'assets/images/berita/a.jpg']);

        $this->assertSame('http://x.test/assets/images/berita/a.jpg', $h['image']);
    }

    public function testGambarUrlPenuhTidakDiubah(): void
    {
        $h = $this->service()->halaman('x', '', ['gambar' => 'https://cdn.test/a.jpg']);

        $this->assertSame('https://cdn.test/a.jpg', $h['image']);
    }

    public function testNoindexMengubahRobots(): void
    {
        $this->assertSame('index, follow', $this->service()->halaman('x')['robots']);
        $this->assertSame('noindex, follow', $this->service()->halaman('x', '', ['noindex' => true])['robots']);
    }

    public function testTanggalTerbitFormatIsoWib(): void
    {
        $h = $this->service()->halaman('x', '', ['terbit' => '2026-10-09 08:00:00']);

        $this->assertSame('2026-10-09T08:00:00+07:00', $h['terbit']);
    }

    public function testTanggalKosongAtauNolMenghasilkanStringKosong(): void
    {
        $this->assertSame('', $this->service()->halaman('x')['terbit']);
        $this->assertSame('', $this->service()->halaman('x', '', ['terbit' => '0000-00-00 00:00:00'])['terbit']);
    }

    public function testPengaturanKosongMemakaiNamaBawaanDanTanpaLogo(): void
    {
        $service = $this->service([]);
        $h       = $service->halaman();

        $this->assertSame('SMK ATAUFIQIYYAH', $h['title']);
        $this->assertSame('', $h['image']);
    }

    // ---------- ringkas() ----------

    public function testRingkasDipotongDiBatas160DenganElipsis(): void
    {
        $hasil = $this->service()->ringkas(str_repeat('kata ', 100));

        $this->assertLessThanOrEqual(160, mb_strlen($hasil));
        $this->assertStringEndsWith('…', $hasil);
    }

    public function testRingkasTeksPendekTidakDipotong(): void
    {
        $this->assertSame('Halo dunia', $this->service()->ringkas('  Halo   dunia '));
    }

    // ---------- JSON-LD ----------

    public function testJsonLdSekolah(): void
    {
        $d = $this->service()->jsonLdSekolah();

        $this->assertSame('EducationalOrganization', $d['@type']);
        $this->assertSame('SMK Tes', $d['name']);
        $this->assertSame('http://x.test/', $d['url']);
        $this->assertSame('021-123', $d['telephone']);
        $this->assertSame('a@b.id', $d['email']);
        $this->assertSame('Jl. Mawar 1', $d['address']['streetAddress']);
        $this->assertSame('http://x.test/assets/uploads/sekolah/logo.png', $d['logo']);
    }

    public function testJsonLdSekolahTanpaDataKontakTidakMemuatFieldKosong(): void
    {
        $d = $this->service(['nama_sekolah' => 'SMK Tes'])->jsonLdSekolah();

        $this->assertArrayNotHasKey('telephone', $d);
        $this->assertArrayNotHasKey('email', $d);
        $this->assertArrayNotHasKey('address', $d);
        $this->assertArrayNotHasKey('logo', $d);
    }

    public function testJsonLdArtikel(): void
    {
        $d = $this->service()->jsonLdArtikel([
            'judul'           => 'Judul Berita',
            'ringkasan'       => '',
            'konten'          => 'Isi konten',
            'tanggal_publish' => '2026-10-09',
            'updated_at'      => null,
        ], 'berita/judul-berita', 'http://x.test/g.jpg');

        $this->assertSame('NewsArticle', $d['@type']);
        $this->assertSame('Judul Berita', $d['headline']);
        $this->assertSame('http://x.test/berita/judul-berita', $d['mainEntityOfPage']['@id']);
        $this->assertSame(['http://x.test/g.jpg'], $d['image']);
        $this->assertSame('2026-10-09T00:00:00+07:00', $d['datePublished']);
        $this->assertSame($d['datePublished'], $d['dateModified']);
        $this->assertSame('Isi konten', $d['description']);
        $this->assertSame('SMK Tes', $d['author']['name']);
    }

    public function testJsonLdArtikelTanpaGambarTidakMemuatImage(): void
    {
        $d = $this->service()->jsonLdArtikel(['judul' => 'J', 'tanggal_publish' => '2026-10-09'], 'berita/j');

        $this->assertArrayNotHasKey('image', $d);
    }

    public function testJsonLdBreadcrumb(): void
    {
        $d = $this->service()->jsonLdBreadcrumb([
            ['nama' => 'Beranda', 'path' => ''],
            ['nama' => 'Berita', 'path' => 'berita'],
        ]);

        $this->assertSame('BreadcrumbList', $d['@type']);
        $this->assertSame(1, $d['itemListElement'][0]['position']);
        $this->assertSame(2, $d['itemListElement'][1]['position']);
        $this->assertSame('http://x.test/berita', $d['itemListElement'][1]['item']);
    }

    public function testJsonLdKeScriptTidakBisaMenutupTagScript(): void
    {
        $teks = '</script><script>alert(1)</script> & é';
        $json = $this->service()->jsonLdKeScript(['x' => $teks]);

        $this->assertStringNotContainsString('</script>', $json);
        $this->assertStringNotContainsString('<', $json);
        $this->assertSame($teks, json_decode($json, true)['x']);
    }

    // ---------- robots.txt & llms.txt ----------

    public function testRobotsTxt(): void
    {
        $robots = $this->service()->robotsTxt();

        $this->assertStringContainsString('User-agent: *', $robots);
        $this->assertStringContainsString('Disallow: /admin/', $robots);
        $this->assertStringContainsString('Disallow: /login', $robots);
        $this->assertStringContainsString('Sitemap: http://x.test/sitemap.xml', $robots);
    }

    public function testLlmsTxt(): void
    {
        $llms = $this->service()->llmsTxt();

        $this->assertStringStartsWith('# SMK Tes', $llms);
        $this->assertStringContainsString('[PPDB](http://x.test/ppdb)', $llms);
        $this->assertStringContainsString('- Alamat: Jl. Mawar 1', $llms);
        $this->assertStringContainsString('- Telepon: 021-123', $llms);
    }

    public function testBaseUrlDenganDanTanpaSlashAkhirSamaHasilnya(): void
    {
        $a = new SeoService($this->model, 'http://x.test');
        $b = new SeoService($this->model, 'http://x.test/');

        $this->assertSame($a->url('berita'), $b->url('berita'));
    }
}
