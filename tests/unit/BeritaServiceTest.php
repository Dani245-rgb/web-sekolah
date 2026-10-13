<?php

namespace Tests\Unit;

use App\Models\AuditLogModel;
use App\Models\BeritaModel;
use App\Services\BeritaService;
use App\Services\FileUploadService;
use App\Services\SlugService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class BeritaServiceTest extends CIUnitTestCase
{
    private const FOLDER = 'assets/images/berita';

    private $model;
    private $audit;
    private $uploader;
    private $slug;
    private BeritaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(BeritaModel::class);
        $this->audit    = $this->createMock(AuditLogModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);
        $this->slug     = $this->createMock(SlugService::class);

        $this->slug->method('buatUnik')->willReturn('judul-berita-baru');

        $this->service = new BeritaService($this->model, $this->audit, $this->uploader, $this->slug);
    }

    private function input(array $timpa = []): array
    {
        return array_merge([
            'judul'    => 'Judul Berita Baru',
            'kategori' => 'Umum',
            'konten'   => 'Isi berita',
            'status'   => 'Draft',
            'posisi'   => 'biasa',
        ], $timpa);
    }

    public function testJudulTerlaluPendekDitolak(): void
    {
        $this->uploader->expects($this->never())->method('simpanKompres');
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah($this->input(['judul' => '  ab  ']), null, 1, 'Admin');
    }

    public function testGambarDitolakUploaderTidakMenyimpanBerita(): void
    {
        $this->uploader->method('simpanKompres')->willThrowException(new InvalidArgumentException('Tipe file tidak diizinkan.'));
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipe file tidak diizinkan.');
        $this->service->tambah($this->input(), null, 1, 'Admin');
    }

    public function testInsertGagalGambarBaruDibuang(): void
    {
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);
        $this->audit->expects($this->never())->method('catat');

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah($this->input(), null, 1, 'Admin');
    }

    public function testTambahBerhasilMencatatAudit(): void
    {
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['judul'] === 'Judul Berita Baru'
                    && $d['slug'] === 'judul-berita-baru'
                    && $d['id_user'] === 7
            ))
            ->willReturn(1);

        $this->audit->expects($this->once())
            ->method('catat')
            ->with(7, 'Admin', 'Tambah Berita', 'Judul:Judul Berita Baru');

        $this->service->tambah($this->input(), null, 7, 'Admin');
    }

    public function testUbahGagalGambarLamaTidakDihapus(): void
    {
        $this->model->method('find')->willReturn([
            'id_berita'       => 1,
            'judul'           => 'Judul Berita Baru',
            'slug'            => 'judul-berita-baru',
            'gambar'          => 'lama.jpg',
            'tanggal_publish' => '2026-01-01',
        ]);
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        // Hanya gambar BARU yang dibuang, gambar lama aman
        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(1, $this->input(), null, 1, 'Admin');
    }

    public function testUbahBerhasilHapusGambarLamaSetelahUpdate(): void
    {
        $this->model->method('find')->willReturn([
            'id_berita'       => 1,
            'judul'           => 'Judul Berita Baru',
            'slug'            => 'judul-berita-baru',
            'gambar'          => 'lama.jpg',
            'tanggal_publish' => '2026-01-01',
        ]);
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', self::FOLDER);
        $this->audit->expects($this->once())->method('catat');

        $this->service->ubah(1, $this->input(), null, 1, 'Admin');
    }
}