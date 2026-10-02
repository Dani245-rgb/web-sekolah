<?php

namespace Tests\Unit;

use App\Models\BkArtikelModel;
use App\Services\BkArtikelService;
use App\Services\FileUploadService;
use App\Services\SlugService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class BkArtikelServiceTest extends CIUnitTestCase
{
    private $model;
    private $uploader;
    private $slug;
    private $koneksi;
    private BkArtikelService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(BkArtikelModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);
        $this->slug     = $this->createMock(SlugService::class);
        $this->koneksi       = $this->createMock(BaseConnection::class);

        $this->koneksi->method('transStatus')->willReturn(true);
        $this->slug->method('buatUnik')->willReturn('tips-karier');

        $this->service = new BkArtikelService($this->model, $this->uploader, $this->slug, $this->koneksi);
    }

    public function testKategoriTidakDikenalDiabaikan(): void
    {
        $this->model->expects($this->once())->method('getDaftarAdmin')->with(null)->willReturn([]);

        $this->service->daftar('kategori-ngawur');
    }

    public function testKategoriValidDiteruskanKeModel(): void
    {
        $this->model->expects($this->once())->method('getDaftarAdmin')->with('karier')->willReturn([]);

        $this->service->daftar('karier');
    }

    public function testTambahJudulDuplikatDitolak(): void
    {
        $this->model->method('judulSudahDipakai')->willReturn(true);
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah(['judul' => 'Tips Karier', 'kategori' => 'karier']);
    }

    public function testTambahStatusDefaultDraft(): void
    {
        $this->model->method('judulSudahDipakai')->willReturn(false);
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['status'] === 'Draft'
                    && $d['slug'] === 'tips-karier'
                    && $d['link_eksternal'] === null
            ))
            ->willReturn(1);

        $this->service->tambah(['judul' => 'Tips Karier', 'kategori' => 'karier']);
    }
}