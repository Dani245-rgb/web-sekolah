<?php

namespace Tests\Unit;

use App\Models\ProfilKontenModel;
use App\Services\FileUploadService;
use App\Services\ProfilSekolahService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class ProfilSekolahServiceTest extends CIUnitTestCase
{
    private const FOLDER = 'profil';

    private $model;
    private $uploader;
    private ProfilSekolahService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(ProfilKontenModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);

        $this->service = new ProfilSekolahService($this->model, $this->uploader);
    }

    private function inputKepala(): array
    {
        return ['nama' => '  Drs.   Ahmad ', 'jabatan' => ' Kepala   Sekolah ', 'konten' => 'Sambutan'];
    }

    private function kepalaLama(): array
    {
        return ['id' => 4, 'jenis' => 'kepala_sekolah', 'foto' => 'lama.jpg'];
    }

    public function testJenisTeksHanyaSejarahDanVisiMisi(): void
    {
        $this->assertTrue($this->service->jenisTeksValid('sejarah'));
        $this->assertTrue($this->service->jenisTeksValid('visi_misi'));
        $this->assertFalse($this->service->jenisTeksValid('kepala_sekolah'));
        $this->assertFalse($this->service->jenisTeksValid('ngawur'));
    }

    public function testSimpanTeksJenisTidakValidDitolak(): void
    {
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Jenis tidak valid.');
        $this->service->simpanTeks('kepala_sekolah', ['judul' => 'x', 'konten' => 'y']);
    }

    public function testSimpanTeksBerhasilJudulDirapikan(): void
    {
        $this->model->method('getOrCreate')->willReturn(['id' => 3, 'jenis' => 'sejarah']);
        $this->model->expects($this->once())
            ->method('update')
            ->with(3, ['judul' => 'Sejarah Kami', 'konten' => "Baris 1\nBaris 2"])
            ->willReturn(true);

        $this->service->simpanTeks('sejarah', ['judul' => '  Sejarah   Kami ', 'konten' => "Baris 1\nBaris 2"]);
    }

    public function testSimpanTeksGagalDbPesanTidakKosong(): void
    {
        $this->model->method('getOrCreate')->willReturn(['id' => 3]);
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gagal menyimpan.');
        $this->service->simpanTeks('sejarah', ['judul' => 'Sejarah', 'konten' => 'Isi']);
    }

    public function testKepalaSekolahFotoDitolakTidakMenyimpan(): void
    {
        $this->model->method('getOrCreate')->willReturn($this->kepalaLama());
        $this->uploader->method('simpan')->willThrowException(new InvalidArgumentException('Tipe file tidak diizinkan.'));
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipe file tidak diizinkan.');
        $this->service->simpanKepalaSekolah($this->inputKepala());
    }

    public function testKepalaSekolahBerhasilNamaDanJabatanDirapikan(): void
    {
        $this->model->method('getOrCreate')->willReturn($this->kepalaLama());
        $this->uploader->method('simpan')->willReturn(null);
        $this->model->expects($this->once())
            ->method('update')
            ->with(4, ['nama' => 'Drs. Ahmad', 'jabatan' => 'Kepala Sekolah', 'konten' => 'Sambutan'])
            ->willReturn(true);

        $this->service->simpanKepalaSekolah($this->inputKepala());
    }

    public function testKepalaSekolahGagalDbFotoBaruDibuangFotoLamaAman(): void
    {
        $this->model->method('getOrCreate')->willReturn($this->kepalaLama());
        $this->uploader->method('simpan')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->service->simpanKepalaSekolah($this->inputKepala());
    }

    public function testKepalaSekolahBerhasilFotoLamaDihapusSetelahUpdate(): void
    {
        $this->model->method('getOrCreate')->willReturn($this->kepalaLama());
        $this->uploader->method('simpan')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', self::FOLDER);

        $this->service->simpanKepalaSekolah($this->inputKepala());
    }

    public function testKepalaSekolahTanpaFotoBaruTidakMenghapusApaPun(): void
    {
        $this->model->method('getOrCreate')->willReturn($this->kepalaLama());
        $this->uploader->method('simpan')->willReturn(null);
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->never())->method('hapus');

        $this->service->simpanKepalaSekolah($this->inputKepala());
    }
}