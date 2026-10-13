<?php

namespace Tests\Unit;

use App\Models\GaleriModel;
use App\Services\FileUploadService;
use App\Services\GaleriService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class GaleriServiceTest extends CIUnitTestCase
{
    private const FOLDER = 'galeri';

    private $model;
    private $uploader;
    private GaleriService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(GaleriModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);

        $this->service = new GaleriService($this->model, $this->uploader);
    }

    private function input(): array
    {
        return [
            'judul'     => '  Upacara   Bendera ',
            'kategori'  => 'Kegiatan',
            'deskripsi' => 'Upacara hari Senin',
            'status'    => 'Published',
        ];
    }

    private function galeriLama(): array
    {
        return ['id' => 1, 'judul' => 'Lama', 'foto' => 'lama.jpg'];
    }

    public function testTambahFotoGagalDiuploadDitolak(): void
    {
        $this->uploader->method('simpanKompres')->willReturn(null);
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Foto gagal diupload.');
        $this->service->tambah($this->input(), null);
    }

    public function testTambahBerhasilJudulDirapikan(): void
    {
        $this->uploader->method('simpanKompres')->willReturn('abc.jpg');
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['judul'] === 'Upacara Bendera' && $d['foto'] === 'abc.jpg'
            ))
            ->willReturn(1);

        $this->service->tambah($this->input(), null);
    }

    public function testTambahGagalDbFotoBaruDibuangDanPesanTidakKosong(): void
    {
        $this->uploader->method('simpanKompres')->willReturn('abc.jpg');
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->uploader->expects($this->once())->method('hapus')->with('abc.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gagal menyimpan foto.');
        $this->service->tambah($this->input(), null);
    }

    public function testUbahGagalFotoLamaTidakDihapus(): void
    {
        $this->model->method('find')->willReturn($this->galeriLama());
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        // Hanya foto BARU yang dibuang, foto lama aman
        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(1, $this->input(), null);
    }

    public function testUbahBerhasilFotoLamaDihapusSetelahUpdate(): void
    {
        $this->model->method('find')->willReturn($this->galeriLama());
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', self::FOLDER);

        $this->service->ubah(1, $this->input(), null);
    }

    public function testUbahTanpaFotoBaruTidakMenghapusApaPun(): void
    {
        $this->model->method('find')->willReturn($this->galeriLama());
        $this->uploader->method('simpanKompres')->willReturn(null);
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->never())->method('hapus');

        $this->service->ubah(1, $this->input(), null);
    }

    public function testHapusMenghapusRecordLaluFile(): void
    {
        $this->model->method('find')->willReturn($this->galeriLama());

        $this->model->expects($this->once())->method('delete')->with(1);
        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', self::FOLDER);

        $this->service->hapus(1);
    }

    public function testHapusDataTidakAdaDitolak(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('delete');
        $this->uploader->expects($this->never())->method('hapus');

        $this->expectException(InvalidArgumentException::class);
        $this->service->hapus(99);
    }
}