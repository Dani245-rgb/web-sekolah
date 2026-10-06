<?php

namespace Tests\Unit;

use App\Models\PendaftarPpdbModel;
use App\Models\PengaturanPpdbModel;
use App\Services\PpdbAdminService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @internal
 */
final class PpdbAdminServiceTest extends CIUnitTestCase
{
    private PendaftarPpdbModel&MockObject $pendaftarModel;
    private PengaturanPpdbModel&MockObject $pengaturanModel;
    private PpdbAdminService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pendaftarModel  = $this->createMock(PendaftarPpdbModel::class);
        $this->pengaturanModel = $this->createMock(PengaturanPpdbModel::class);
        $this->service         = new PpdbAdminService($this->pendaftarModel, $this->pengaturanModel);
    }

    // ---------- daftar / ambil ----------

    public function testDaftarMengambilDariModel(): void
    {
        $this->pendaftarModel->expects($this->once())->method('daftarTerbaru')->willReturn([['id' => 1], ['id' => 2]]);

        $this->assertCount(2, $this->service->daftar());
    }

    public function testAmbilMengembalikanDataKetemu(): void
    {
        $this->pendaftarModel->method('find')->with(5)->willReturn(['id' => 5]);

        $this->assertSame(['id' => 5], $this->service->ambil(5));
    }

    public function testAmbilMelemparKalauTidakAda(): void
    {
        $this->pendaftarModel->method('find')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data tidak ditemukan.');
        $this->service->ambil(99);
    }

    // ---------- ubahStatus ----------

    public function testUbahStatusBerhasil(): void
    {
        $this->pendaftarModel->method('find')->willReturn(['id' => 3]);
        $this->pendaftarModel->expects($this->once())->method('ubahStatus')->with(3, 'Diterima')->willReturn(true);

        $this->service->ubahStatus(3, 'Diterima');
    }

    public function testUbahStatusDataTidakAda(): void
    {
        $this->pendaftarModel->method('find')->willReturn(null);
        $this->pendaftarModel->expects($this->never())->method('ubahStatus');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubahStatus(3, 'Diterima');
    }

    public function testUbahStatusTidakValid(): void
    {
        $this->pendaftarModel->method('find')->willReturn(['id' => 3]);
        $this->pendaftarModel->expects($this->never())->method('ubahStatus');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status tidak valid.');
        $this->service->ubahStatus(3, 'Asal');
    }

    public function testUbahStatusGagalSimpanJadiRuntime(): void
    {
        $this->pendaftarModel->method('find')->willReturn(['id' => 3]);
        $this->pendaftarModel->method('ubahStatus')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->service->ubahStatus(3, 'Ditolak');
    }

    // ---------- hapus ----------

    public function testHapusBerhasil(): void
    {
        $this->pendaftarModel->method('find')->willReturn(['id' => 7]);
        $this->pendaftarModel->expects($this->once())->method('delete')->with(7)->willReturn(true);

        $this->service->hapus(7);
    }

    public function testHapusDataTidakAda(): void
    {
        $this->pendaftarModel->method('find')->willReturn(null);
        $this->pendaftarModel->expects($this->never())->method('delete');

        $this->expectException(InvalidArgumentException::class);
        $this->service->hapus(7);
    }

    public function testHapusGagalJadiRuntime(): void
    {
        $this->pendaftarModel->method('find')->willReturn(['id' => 7]);
        $this->pendaftarModel->method('delete')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->service->hapus(7);
    }

    // ---------- pengaturan ----------

    public function testPengaturanMengambilDariModel(): void
    {
        $this->pengaturanModel->method('getSetting')->willReturn(['status' => 'tutup']);

        $this->assertSame(['status' => 'tutup'], $this->service->pengaturan());
    }

    public function testSimpanPengaturanBukaDenganTahunAjaran(): void
    {
        $this->pengaturanModel->expects($this->once())->method('simpan')
            ->with([
                'status'       => 'buka',
                'pesan_tutup'  => 'x',
                'tahun_ajaran' => '2026/2027',
            ])->willReturn(true);

        $this->service->simpanPengaturan(['status' => 'buka', 'pesan_tutup' => 'x', 'tahun_ajaran' => '2026/2027']);
    }

    public function testSimpanPengaturanTutupTanpaTahunAjaranMenyimpanNull(): void
    {
        $this->pengaturanModel->expects($this->once())->method('simpan')
            ->with($this->callback(static fn (array $d): bool => $d['status'] === 'tutup' && $d['tahun_ajaran'] === null))
            ->willReturn(true);

        $this->service->simpanPengaturan(['status' => 'tutup', 'pesan_tutup' => '', 'tahun_ajaran' => '']);
    }

    public function testSimpanPengaturanBukaTanpaTahunAjaranDitolak(): void
    {
        $this->pengaturanModel->expects($this->never())->method('simpan');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Isi tahun ajaran dulu sebelum membuka pendaftaran.');
        $this->service->simpanPengaturan(['status' => 'buka', 'tahun_ajaran' => '   ']);
    }

    public function testSimpanPengaturanStatusKosong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->simpanPengaturan(['status' => '']);
    }

    public function testSimpanPengaturanStatusTidakValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->simpanPengaturan(['status' => 'setengah']);
    }

    public function testSimpanPengaturanPesanTutupTerlaluPanjang(): void
    {
        $this->pengaturanModel->expects($this->never())->method('simpan');

        $this->expectException(InvalidArgumentException::class);
        $this->service->simpanPengaturan(['status' => 'tutup', 'pesan_tutup' => str_repeat('a', 256)]);
    }

    public function testSimpanPengaturanTahunAjaranTerlaluPanjang(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->simpanPengaturan(['status' => 'tutup', 'tahun_ajaran' => str_repeat('1', 21)]);
    }

    public function testSimpanPengaturanGagalSimpanJadiRuntime(): void
    {
        $this->pengaturanModel->method('simpan')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->service->simpanPengaturan(['status' => 'tutup']);
    }
}