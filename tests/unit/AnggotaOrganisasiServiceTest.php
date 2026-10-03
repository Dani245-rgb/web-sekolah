<?php

namespace Tests\Unit;

use App\Models\AnggotaOrganisasiModel;
use App\Models\OrganisasiModel;
use App\Services\AnggotaOrganisasiService;
use App\Services\FileUploadService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class AnggotaOrganisasiServiceTest extends CIUnitTestCase
{
    private const FOLDER = 'organisasi';

    private $model;
    private $organisasiModel;
    private $uploader;
    private AnggotaOrganisasiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model           = $this->createMock(AnggotaOrganisasiModel::class);
        $this->organisasiModel = $this->createMock(OrganisasiModel::class);
        $this->uploader        = $this->createMock(FileUploadService::class);

        $this->service = new AnggotaOrganisasiService($this->model, $this->organisasiModel, $this->uploader);
    }

    private function input(): array
    {
        return ['nama' => '  Budi   Santoso ', 'jabatan' => ' Ketua ', 'urutan' => '2'];
    }

    private function anggotaLama(int $idOrganisasi = 1): array
    {
        return ['id' => 5, 'id_organisasi' => $idOrganisasi, 'nama' => 'Budi', 'foto' => 'lama.jpg'];
    }

    public function testCariAnggotaMilikOrganisasiLainDianggapTidakAda(): void
    {
        $this->model->method('find')->willReturn($this->anggotaLama(2));

        $this->assertNull($this->service->cariAnggota(1, 5));
    }

    public function testCariAnggotaMilikOrganisasiYangBenarDitemukan(): void
    {
        $this->model->method('find')->willReturn($this->anggotaLama(1));

        $this->assertSame(5, $this->service->cariAnggota(1, 5)['id']);
    }

    public function testTambahOrganisasiTidakAdaDitolak(): void
    {
        $this->organisasiModel->method('find')->willReturn(null);
        $this->uploader->expects($this->never())->method('simpan');
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah(99, $this->input());
    }

    public function testTambahBerhasilDataDirapikanDanFotoOpsional(): void
    {
        $this->organisasiModel->method('find')->willReturn(['id' => 1]);
        $this->uploader->method('simpan')->willReturn(null);
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['id_organisasi'] === 1
                    && $d['nama'] === 'Budi Santoso'
                    && $d['jabatan'] === 'Ketua'
                    && $d['urutan'] === 2
                    && $d['foto'] === null
            ))
            ->willReturn(1);

        $this->service->tambah(1, $this->input());
    }

    public function testTambahGagalDbFotoBaruDibuang(): void
    {
        $this->organisasiModel->method('find')->willReturn(['id' => 1]);
        $this->uploader->method('simpan')->willReturn('baru.jpg');
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gagal menyimpan anggota.');
        $this->service->tambah(1, $this->input());
    }

    public function testUbahAnggotaOrganisasiLainDitolak(): void
    {
        $this->model->method('find')->willReturn($this->anggotaLama(2));
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(1, 5, $this->input());
    }

    public function testUbahGagalFotoLamaTidakDihapus(): void
    {
        $this->model->method('find')->willReturn($this->anggotaLama());
        $this->uploader->method('simpan')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        // Hanya foto BARU yang dibuang, foto lama aman
        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(1, 5, $this->input());
    }

    public function testUbahBerhasilFotoLamaDihapusSetelahUpdate(): void
    {
        $this->model->method('find')->willReturn($this->anggotaLama());
        $this->uploader->method('simpan')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', self::FOLDER);

        $this->service->ubah(1, 5, $this->input());
    }

    public function testUbahTanpaFotoBaruTidakMenghapusApaPun(): void
    {
        $this->model->method('find')->willReturn($this->anggotaLama());
        $this->uploader->method('simpan')->willReturn(null);
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->never())->method('hapus');

        $this->service->ubah(1, 5, $this->input());
    }

    public function testHapusMenghapusRecordLaluFile(): void
    {
        $this->model->method('find')->willReturn($this->anggotaLama());

        $this->model->expects($this->once())->method('delete')->with(5);
        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', self::FOLDER);

        $this->service->hapus(1, 5);
    }

    public function testHapusAnggotaOrganisasiLainTidakMelakukanApaPun(): void
    {
        $this->model->method('find')->willReturn($this->anggotaLama(2));

        $this->model->expects($this->never())->method('delete');
        $this->uploader->expects($this->never())->method('hapus');

        $this->service->hapus(1, 5);
    }
}