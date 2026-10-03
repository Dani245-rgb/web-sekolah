<?php

namespace Tests\Unit;

use App\Models\AnggotaOrganisasiModel;
use App\Models\OrganisasiModel;
use App\Services\FileUploadService;
use App\Services\OrganisasiService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class OrganisasiServiceTest extends CIUnitTestCase
{
    private const FOLDER = 'organisasi';

    private $model;
    private $anggotaModel;
    private $uploader;
    private OrganisasiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model        = $this->createMock(OrganisasiModel::class);
        $this->anggotaModel = $this->createMock(AnggotaOrganisasiModel::class);
        $this->uploader     = $this->createMock(FileUploadService::class);

        $this->service = new OrganisasiService($this->model, $this->anggotaModel, $this->uploader);
    }

    private function input(): array
    {
        return ['nama' => '  OSIS   SMK ', 'deskripsi' => 'Organisasi siswa', 'status' => 'Published'];
    }

    public function testTambahNamaDuplikatDitolak(): void
    {
        $this->model->method('namaSudahAda')->willReturn(true);
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama organisasi sudah dipakai.');
        $this->service->tambah($this->input());
    }

    public function testTambahBerhasilNamaDirapikan(): void
    {
        $this->model->method('namaSudahAda')->willReturn(false);
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(static fn (array $d) => $d['nama'] === 'OSIS SMK'))
            ->willReturn(1);

        $this->service->tambah($this->input());
    }

    public function testTambahGagalDbPesanTidakKosong(): void
    {
        $this->model->method('namaSudahAda')->willReturn(false);
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gagal menyimpan organisasi.');
        $this->service->tambah($this->input());
    }

    public function testUbahCekNamaDenganMengecualikanDirinyaSendiri(): void
    {
        $this->model->method('find')->willReturn(['id' => 1, 'nama' => 'OSIS SMK']);
        $this->model->expects($this->once())->method('namaSudahAda')->with('OSIS SMK', 1)->willReturn(true);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(1, $this->input());
    }

    public function testUbahDataTidakAdaDitolak(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(99, $this->input());
    }

    public function testHapusMenghapusRecordLaluFotoSemuaAnggota(): void
    {
        $this->model->method('find')->willReturn(['id' => 1]);
        $this->anggotaModel->method('getByOrganisasi')->willReturn([
            ['id' => 10, 'foto' => 'a.jpg'],
            ['id' => 11, 'foto' => null],
            ['id' => 12, 'foto' => 'c.jpg'],
        ]);

        $this->model->expects($this->once())->method('delete')->with(1);

        $dihapus = [];
        $this->uploader->method('hapus')->willReturnCallback(
            static function ($nama, $folder) use (&$dihapus) {
                $dihapus[] = [$nama, $folder];
            }
        );

        $this->service->hapus(1);

        $this->assertSame([['a.jpg', self::FOLDER], [null, self::FOLDER], ['c.jpg', self::FOLDER]], $dihapus);
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