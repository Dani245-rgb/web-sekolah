<?php

namespace Tests\Unit;

use App\Models\PesanKontakModel;
use App\Services\KontakAdminService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @internal
 */
final class KontakAdminServiceTest extends CIUnitTestCase
{
    private PesanKontakModel&MockObject $model;
    private KontakAdminService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model   = $this->createMock(PesanKontakModel::class);
        $this->service = new KontakAdminService($this->model);
    }

    // ---------- daftar / ambil ----------

    public function testDaftarMengambilDariModel(): void
    {
        $this->model->expects($this->once())->method('daftarTerbaru')->willReturn([['id' => 1], ['id' => 2]]);

        $this->assertCount(2, $this->service->daftar());
    }

    public function testAmbilKetemu(): void
    {
        $this->model->method('find')->with(4)->willReturn(['id' => 4]);

        $this->assertSame(['id' => 4], $this->service->ambil(4));
    }

    public function testAmbilTidakAdaMelempar(): void
    {
        $this->model->method('find')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(KontakAdminService::TIDAK_DITEMUKAN);
        $this->service->ambil(9);
    }

    // ---------- lihat ----------

    public function testLihatPesanBelumDibacaDitandaiDibaca(): void
    {
        $this->model->method('find')->willReturn(['id' => 5, 'status' => 'Belum Dibaca']);
        $this->model->expects($this->once())->method('tandaiSudahDibaca')->with(5)->willReturn(true);

        $this->assertSame('Sudah Dibaca', $this->service->lihat(5)['status']);
    }

    public function testLihatPesanSudahDibacaTidakDiubahLagi(): void
    {
        $this->model->method('find')->willReturn(['id' => 5, 'status' => 'Sudah Dibaca']);
        $this->model->expects($this->never())->method('tandaiSudahDibaca');

        $this->assertSame('Sudah Dibaca', $this->service->lihat(5)['status']);
    }

    public function testLihatTetapTampilMeskiGagalMenandai(): void
    {
        $this->model->method('find')->willReturn(['id' => 5, 'status' => 'Belum Dibaca']);
        $this->model->method('tandaiSudahDibaca')->willReturn(false);

        $this->assertSame('Belum Dibaca', $this->service->lihat(5)['status']);
    }

    public function testLihatTidakAdaMelempar(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('tandaiSudahDibaca');

        $this->expectException(InvalidArgumentException::class);
        $this->service->lihat(5);
    }

    // ---------- hapus ----------

    public function testHapusBerhasil(): void
    {
        $this->model->method('find')->willReturn(['id' => 8]);
        $this->model->expects($this->once())->method('delete')->with(8)->willReturn(true);

        $this->service->hapus(8);
    }

    public function testHapusDataTidakAda(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('delete');

        $this->expectException(InvalidArgumentException::class);
        $this->service->hapus(8);
    }

    public function testHapusGagalJadiRuntime(): void
    {
        $this->model->method('find')->willReturn(['id' => 8]);
        $this->model->method('delete')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->service->hapus(8);
    }
}