<?php

namespace Tests\Unit;

use App\Models\JurusanModel;
use App\Services\FileUploadService;
use App\Services\JurusanService;
use App\Services\SlugService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use RuntimeException;

/**
 * @internal
 */
final class JurusanServiceTest extends CIUnitTestCase
{
    private $model;
    private $uploader;
    private $slug;
    private $koneksi;
    private JurusanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(JurusanModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);
        $this->slug     = $this->createMock(SlugService::class);
        $this->koneksi       = $this->createMock(BaseConnection::class);

        $this->koneksi->method('transStatus')->willReturn(true);
        $this->slug->method('buatUnik')->willReturn('teknik-komputer');

        $this->service = new JurusanService($this->model, $this->uploader, $this->slug, $this->koneksi);
    }

    public function testTambahNamaKosongDitolak(): void
    {
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah(['nama_jurusan' => '    ']);
    }

    public function testTambahNamaDuplikatDitolak(): void
    {
        $this->model->method('namaSudahDipakai')->willReturn(true);
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah(['nama_jurusan' => 'Teknik Komputer']);
    }

    public function testTambahBerhasilMenormalisasiNamaDanMembuatSlug(): void
    {
        $this->model->method('namaSudahDipakai')->willReturn(false);
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['nama_jurusan'] === 'Teknik Komputer'
                    && $d['slug'] === 'teknik-komputer'
            ))
            ->willReturn(1);

        $this->service->tambah(['nama_jurusan' => "  Teknik   Komputer  "]);
    }

    public function testTambahGagalInsertRollbackDanFotoDibuang(): void
    {
        $this->model->method('namaSudahDipakai')->willReturn(false);
        $this->model->method('insert')->willReturn(false);
        $this->uploader->method('simpan')->willReturn('foto-baru.jpg');

        $this->koneksi->expects($this->once())->method('transRollback');
        $this->uploader->expects($this->once())->method('hapus')->with('foto-baru.jpg', 'jurusan');

        $this->expectException(RuntimeException::class);
        $this->service->tambah(['nama_jurusan' => 'Teknik Komputer']);
    }

    public function testUbahFotoLamaDihapusSetelahSukses(): void
    {
        $this->model->method('find')->willReturn([
            'id_jurusan'   => 1,
            'nama_jurusan' => 'Teknik Komputer',
            'foto'         => 'lama.jpg',
        ]);
        $this->model->method('namaSudahDipakai')->willReturn(false);
        $this->model->method('update')->willReturn(true);
        $this->uploader->method('simpan')->willReturn('baru.jpg');

        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', 'jurusan');

        $this->service->ubah(1, ['nama_jurusan' => 'Teknik Komputer']);
    }
}