<?php

namespace Tests\Unit;

use App\Models\AgendaModel;
use App\Services\AgendaService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @internal
 */
final class AgendaServiceTest extends CIUnitTestCase
{
    private AgendaModel&MockObject $model;
    private AgendaService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model   = $this->createMock(AgendaModel::class);
        $this->service = new AgendaService($this->model);
    }

    private function input(array $timpa = []): array
    {
        return array_merge([
            'judul'   => '  Upacara   Bendera ',
            'tanggal' => '2026-10-17',
            'waktu'   => ' 07.00   WIB ',
            'lokasi'  => '  Lapangan   Utama ',
            'status'  => 'Published',
        ], $timpa);
    }

    // ---------- daftar / ambil ----------

    public function testDaftarMengambilDariModel(): void
    {
        $this->model->expects($this->once())->method('daftarTerbaru')->willReturn([['id' => 1]]);

        $this->assertCount(1, $this->service->daftar());
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
        $this->expectExceptionMessage(AgendaService::TIDAK_DITEMUKAN);
        $this->service->ambil(9);
    }

    // ---------- tambah ----------

    public function testTambahBerhasilMenyimpanDataRapi(): void
    {
        $this->model->expects($this->once())->method('insert')
            ->with([
                'judul'   => 'Upacara Bendera',
                'tanggal' => '2026-10-17',
                'waktu'   => '07.00 WIB',
                'lokasi'  => 'Lapangan Utama',
                'status'  => 'Published',
            ])->willReturn(5);

        $this->assertSame(5, $this->service->tambah($this->input()));
    }

    public function testTambahWaktuDanLokasiBolehKosong(): void
    {
        $this->model->expects($this->once())->method('insert')
            ->with($this->callback(static fn (array $d): bool => $d['waktu'] === '' && $d['lokasi'] === ''))
            ->willReturn(1);

        $this->service->tambah($this->input(['waktu' => '', 'lokasi' => '  ']));
    }

    public function testTambahJudulKosong(): void
    {
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Judul wajib diisi.');
        $this->service->tambah($this->input(['judul' => '   ']));
    }

    public function testTambahJudulTerlaluPendek(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Judul minimal 3 karakter.');
        $this->service->tambah($this->input(['judul' => 'ab']));
    }

    public function testTambahJudulTerlaluPanjang(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Judul maksimal 255 karakter.');
        $this->service->tambah($this->input(['judul' => str_repeat('a', 256)]));
    }

    public function testTambahTanggalKosong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal wajib diisi.');
        $this->service->tambah($this->input(['tanggal' => '']));
    }

    public function testTambahTanggalTidakValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal tidak valid.');
        $this->service->tambah($this->input(['tanggal' => 'bukan-tanggal']));
    }

    public function testTambahStatusTidakValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status harus Published atau Draft.');
        $this->service->tambah($this->input(['status' => 'Arsip']));
    }

    public function testTambahSemuaPesanErrorDikumpulkan(): void
    {
        try {
            $this->service->tambah([]);
            $this->fail('Harus melempar InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Judul wajib diisi.', $e->getMessage());
            $this->assertStringContainsString('Tanggal wajib diisi.', $e->getMessage());
            $this->assertStringContainsString('Status harus Published atau Draft.', $e->getMessage());
        }
    }

    public function testTambahGagalDatabaseJadiRuntime(): void
    {
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->service->tambah($this->input());
    }

    public function testTambahDitolakValidasiModelJadiPesanAman(): void
    {
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn(['judul' => 'Judul bermasalah.']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Judul bermasalah.');
        $this->service->tambah($this->input());
    }

    // ---------- ubah ----------

    public function testUbahBerhasil(): void
    {
        $this->model->method('find')->willReturn(['id' => 3]);
        $this->model->expects($this->once())->method('update')
            ->with(3, $this->callback(static fn (array $d): bool => $d['judul'] === 'Upacara Bendera'))
            ->willReturn(true);

        $this->service->ubah(3, $this->input());
    }

    public function testUbahDataTidakAda(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(AgendaService::TIDAK_DITEMUKAN);
        $this->service->ubah(3, $this->input());
    }

    public function testUbahInputTidakValid(): void
    {
        $this->model->method('find')->willReturn(['id' => 3]);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(3, $this->input(['status' => 'Arsip']));
    }

    public function testUbahGagalDatabaseJadiRuntime(): void
    {
        $this->model->method('find')->willReturn(['id' => 3]);
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->service->ubah(3, $this->input());
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