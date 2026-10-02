<?php

namespace Tests\Unit;

use App\Models\PartnerModel;
use App\Services\FileUploadService;
use App\Services\PartnerService;
use App\Services\SlugService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class PartnerServiceTest extends CIUnitTestCase
{
    private const FOLDER = 'partner';

    private $model;
    private $uploader;
    private $slug;
    private PartnerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(PartnerModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);
        $this->slug     = $this->createMock(SlugService::class);

        $this->slug->method('buatUnik')->willReturn('mitra-industri');

        $this->service = new PartnerService($this->model, $this->uploader, $this->slug);
    }

    private function input(): array
    {
        return [
            'nama'      => '  Mitra   Industri ',
            'deskripsi' => 'Mitra PKL',
            'status'    => 'Published',
        ];
    }

    private function partnerLama(): array
    {
        return ['id' => 1, 'nama' => 'Mitra Industri', 'foto' => 'lama.jpg'];
    }

    public function testTambahNamaDuplikatDitolak(): void
    {
        $this->model->method('namaSudahAda')->willReturn(true);
        $this->uploader->expects($this->never())->method('simpan');
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama partner sudah dipakai.');
        $this->service->tambah($this->input(), null);
    }

    public function testTambahFotoGagalDiuploadDitolak(): void
    {
        $this->model->method('namaSudahAda')->willReturn(false);
        $this->uploader->method('simpan')->willReturn(null);
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Foto gagal diupload.');
        $this->service->tambah($this->input(), null);
    }

    public function testTambahBerhasilNamaDirapikanDanSlugDibuat(): void
    {
        $this->model->method('namaSudahAda')->willReturn(false);
        $this->uploader->method('simpan')->willReturn('abc.jpg');
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['nama'] === 'Mitra Industri'
                    && $d['slug'] === 'mitra-industri'
                    && $d['foto'] === 'abc.jpg'
            ))
            ->willReturn(1);

        $this->service->tambah($this->input(), null);
    }

    public function testTambahGagalDbFotoBaruDibuang(): void
    {
        $this->model->method('namaSudahAda')->willReturn(false);
        $this->uploader->method('simpan')->willReturn('abc.jpg');
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->uploader->expects($this->once())->method('hapus')->with('abc.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gagal menyimpan partner.');
        $this->service->tambah($this->input(), null);
    }

    public function testUbahCekNamaDenganMengecualikanDirinyaSendiri(): void
    {
        $this->model->method('find')->willReturn($this->partnerLama());
        $this->model->expects($this->once())->method('namaSudahAda')->with('Mitra Industri', 1)->willReturn(true);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(1, $this->input(), null);
    }

    public function testUbahGagalFotoLamaTidakDihapus(): void
    {
        $this->model->method('find')->willReturn($this->partnerLama());
        $this->model->method('namaSudahAda')->willReturn(false);
        $this->uploader->method('simpan')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        // Hanya foto BARU yang dibuang, foto lama aman
        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(1, $this->input(), null);
    }

    public function testUbahBerhasilFotoLamaDihapusSetelahUpdate(): void
    {
        $this->model->method('find')->willReturn($this->partnerLama());
        $this->model->method('namaSudahAda')->willReturn(false);
        $this->uploader->method('simpan')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', self::FOLDER);

        $this->service->ubah(1, $this->input(), null);
    }

    public function testUbahTanpaFotoBaruTidakMenghapusApaPun(): void
    {
        $this->model->method('find')->willReturn($this->partnerLama());
        $this->model->method('namaSudahAda')->willReturn(false);
        $this->uploader->method('simpan')->willReturn(null);
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->never())->method('hapus');

        $this->service->ubah(1, $this->input(), null);
    }

    public function testHapusMenghapusRecordLaluFile(): void
    {
        $this->model->method('find')->willReturn($this->partnerLama());

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