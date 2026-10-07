<?php

namespace Tests\Unit;

use App\Models\PengumumanModel;
use App\Services\PengumumanService;
use App\Services\SlugService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @internal
 */
final class PengumumanServiceTest extends CIUnitTestCase
{
    private PengumumanModel&MockObject $model;
    private SlugService&MockObject $slug;
    private PengumumanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model   = $this->createMock(PengumumanModel::class);
        $this->slug    = $this->createMock(SlugService::class);
        $this->service = new PengumumanService($this->model, $this->slug);
    }

    private function input(array $timpa = []): array
    {
        return array_merge([
            'judul'           => '  Libur   Semester  ',
            'isi'             => 'Sekolah libur.',
            'tanggal_publish' => '2026-10-06',
            'status'          => 'Published',
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
        $this->expectExceptionMessage(PengumumanService::TIDAK_DITEMUKAN);
        $this->service->ambil(9);
    }

    // ---------- tambah ----------

    public function testTambahBerhasilMenyimpanJudulRapiDanSlug(): void
    {
        $this->model->method('judulSudahAda')->willReturn(false);
        $this->slug->expects($this->once())->method('buatUnik')
            ->with('Libur Semester', 'pengumuman', 'id', null, 'pengumuman')
            ->willReturn('libur-semester');
        $this->model->expects($this->once())->method('insert')
            ->with([
                'judul'           => 'Libur Semester',
                'isi'             => 'Sekolah libur.',
                'tanggal_publish' => '2026-10-06',
                'status'          => 'Published',
                'slug'            => 'libur-semester',
            ])->willReturn(12);

        $this->assertSame(12, $this->service->tambah($this->input()));
    }

    public function testTambahJudulKembar(): void
    {
        $this->model->method('judulSudahAda')->with('Libur Semester', null)->willReturn(true);
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Judul pengumuman sudah dipakai.');
        $this->service->tambah($this->input());
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

    public function testTambahIsiKosong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Isi pengumuman wajib diisi.');
        $this->service->tambah($this->input(['isi' => '  ']));
    }

    public function testTambahTanggalKosong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal publish wajib diisi.');
        $this->service->tambah($this->input(['tanggal_publish' => '']));
    }

    public function testTambahTanggalTidakValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal publish tidak valid.');
        $this->service->tambah($this->input(['tanggal_publish' => 'bukan-tanggal']));
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
            $this->service->tambah(['judul' => '', 'isi' => '', 'tanggal_publish' => '', 'status' => 'x']);
            $this->fail('Harus melempar InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Judul wajib diisi.', $e->getMessage());
            $this->assertStringContainsString('Isi pengumuman wajib diisi.', $e->getMessage());
            $this->assertStringContainsString('Tanggal publish wajib diisi.', $e->getMessage());
            $this->assertStringContainsString('Status harus Published atau Draft.', $e->getMessage());
        }
    }

    public function testTambahGagalDatabaseJadiRuntime(): void
    {
        $this->model->method('judulSudahAda')->willReturn(false);
        $this->slug->method('buatUnik')->willReturn('x');
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->service->tambah($this->input());
    }

    public function testTambahDitolakValidasiModelJadiPesanAman(): void
    {
        $this->model->method('judulSudahAda')->willReturn(false);
        $this->slug->method('buatUnik')->willReturn('x');
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn(['isi' => 'Isi bermasalah.']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Isi bermasalah.');
        $this->service->tambah($this->input());
    }

    // ---------- ubah ----------

    public function testUbahBerhasil(): void
    {
        $this->model->method('find')->willReturn(['id' => 3]);
        $this->model->method('judulSudahAda')->with('Libur Semester', 3)->willReturn(false);
        $this->slug->expects($this->once())->method('buatUnik')
            ->with('Libur Semester', 'pengumuman', 'id', 3, 'pengumuman')
            ->willReturn('libur-semester');
        $this->model->expects($this->once())->method('update')
            ->with(3, $this->callback(static fn (array $d): bool => $d['slug'] === 'libur-semester' && $d['judul'] === 'Libur Semester'))
            ->willReturn(true);

        $this->service->ubah(3, $this->input());
    }

    public function testUbahDataTidakAda(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(PengumumanService::TIDAK_DITEMUKAN);
        $this->service->ubah(3, $this->input());
    }

    public function testUbahJudulKembarDenganDataLain(): void
    {
        $this->model->method('find')->willReturn(['id' => 3]);
        $this->model->method('judulSudahAda')->willReturn(true);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Judul pengumuman sudah dipakai.');
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
        $this->model->method('judulSudahAda')->willReturn(false);
        $this->slug->method('buatUnik')->willReturn('x');
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