<?php

namespace Tests\Unit;

use App\Libraries\ImageCompressor;
use App\Models\JurusanModel;
use App\Models\SiswaModel;
use App\Services\SiswaService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SiswaServiceTest extends CIUnitTestCase
{
    private $model;
    private $jurusan;
    private $compressor;
    private SiswaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model      = $this->createMock(SiswaModel::class);
        $this->jurusan    = $this->createMock(JurusanModel::class);
        $this->compressor = $this->createMock(ImageCompressor::class);

        $this->model->method('skipValidation')->willReturnSelf();

        $this->service = new SiswaService($this->model, $this->jurusan, $this->compressor);
    }

    public function testFilterJurusanBukanAngkaDiabaikan(): void
    {
        $this->model->expects($this->once())
            ->method('cariAdmin')
            ->with(null, null)
            ->willReturnSelf();
        $this->model->method('paginate')->willReturn([]);

        $hasil = $this->service->daftar('', 'abc');

        $this->assertNull($hasil['jurusanAktif']);
    }

    public function testFilterJurusanAngkaDiteruskan(): void
    {
        $this->model->expects($this->once())
            ->method('cariAdmin')
            ->with('budi', 3)
            ->willReturnSelf();
        $this->model->method('paginate')->willReturn([]);

        $hasil = $this->service->daftar('budi', '3');

        $this->assertSame('3', $hasil['jurusanAktif']);
    }

    public function testBuatSiswaBaruGagalMengembalikanPesanDariModel(): void
    {
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn(['nis' => 'NIS sudah ada']);

        $hasil = $this->service->tambah([
            'nis'           => '2024001',
            'nisn'          => '0012345678',
            'nama'          => 'Budi',
            'jenis_kelamin' => 'L',
            'email'         => '',
        ]);

        $this->assertFalse($hasil['sukses']);
        $this->assertSame('NIS sudah ada', $hasil['pesan']);
    }

    public function testHapusSiswaTidakAdaDitolak(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('delete');

        $hasil = $this->service->hapus(99);

        $this->assertFalse($hasil['sukses']);
    }

    public function testPulihkanSiswaMemakaiDataTongSampah(): void
    {
        $this->model->method('cariTerhapus')->willReturn(['id_siswa' => 5, 'nama' => 'Budi']);
        $this->model->expects($this->once())->method('update')->with(5, ['deleted_at' => null])->willReturn(true);

        $hasil = $this->service->pulihkan(5);

        $this->assertTrue($hasil['sukses']);
        $this->assertStringContainsString('Budi', $hasil['pesan']);
    }
}