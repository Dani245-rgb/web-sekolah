<?php

namespace Tests\Unit;

use App\Models\KalenderAkademikModel;
use App\Services\KalenderAkademikService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @internal
 */
final class KalenderAkademikServiceTest extends CIUnitTestCase
{
    private KalenderAkademikModel&MockObject $model;
    private KalenderAkademikService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model   = $this->createMock(KalenderAkademikModel::class);
        $this->service = new KalenderAkademikService($this->model);
    }

    private function input(array $timpa = []): array
    {
        return array_merge([
            'kegiatan'        => '  Ujian   Tengah Semester ',
            'tanggal_mulai'   => '2026-10-12',
            'tanggal_selesai' => '2026-10-16',
            'keterangan'      => 'Semua kelas',
            'semester'        => 'Ganjil',
            'tahun_ajaran'    => ' 2026/2027 ',
            'status'          => 'Published',
        ], $timpa);
    }

    // ---------- daftar / ambil ----------

    public function testDaftarMengambilDariModel(): void
    {
        $this->model->expects($this->once())->method('daftarUrut')->willReturn([['id' => 1]]);

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
        $this->expectExceptionMessage(KalenderAkademikService::TIDAK_DITEMUKAN);
        $this->service->ambil(9);
    }

    // ---------- tambah ----------

    public function testTambahBerhasilMenyimpanDataRapi(): void
    {
        $this->model->expects($this->once())->method('insert')
            ->with([
                'kegiatan'        => 'Ujian Tengah Semester',
                'tanggal_mulai'   => '2026-10-12',
                'tanggal_selesai' => '2026-10-16',
                'keterangan'      => 'Semua kelas',
                'semester'        => 'Ganjil',
                'tahun_ajaran'    => '2026/2027',
                'status'          => 'Published',
            ])->willReturn(7);

        $this->assertSame(7, $this->service->tambah($this->input()));
    }

    public function testTambahTanggalSelesaiKosongJadiNull(): void
    {
        $this->model->expects($this->once())->method('insert')
            ->with($this->callback(static fn (array $d): bool => $d['tanggal_selesai'] === null))
            ->willReturn(1);

        $this->service->tambah($this->input(['tanggal_selesai' => '']));
    }

    public function testTambahTanggalSelesaiSamaDenganMulaiBoleh(): void
    {
        $this->model->method('insert')->willReturn(1);

        $this->assertSame(1, $this->service->tambah($this->input(['tanggal_selesai' => '2026-10-12'])));
    }

    public function testTambahTanggalSelesaiLebihAwalDitolak(): void
    {
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
        $this->service->tambah($this->input(['tanggal_selesai' => '2026-10-01']));
    }

    public function testTambahKegiatanKosong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kegiatan wajib diisi.');
        $this->service->tambah($this->input(['kegiatan' => '  ']));
    }

    public function testTambahKegiatanTerlaluPendek(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kegiatan minimal 3 karakter.');
        $this->service->tambah($this->input(['kegiatan' => 'ab']));
    }

    public function testTambahKegiatanTerlaluPanjang(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kegiatan maksimal 255 karakter.');
        $this->service->tambah($this->input(['kegiatan' => str_repeat('a', 256)]));
    }

    public function testTambahTanggalMulaiKosong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal mulai wajib diisi.');
        $this->service->tambah($this->input(['tanggal_mulai' => '']));
    }

    public function testTambahTanggalMulaiTidakValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal mulai tidak valid.');
        $this->service->tambah($this->input(['tanggal_mulai' => 'bukan-tanggal']));
    }

    public function testTambahTanggalSelesaiTidakValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal selesai tidak valid.');
        $this->service->tambah($this->input(['tanggal_selesai' => 'bukan-tanggal']));
    }

    public function testTambahSemesterTidakValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Semester harus Ganjil atau Genap.');
        $this->service->tambah($this->input(['semester' => 'Pendek']));
    }

    public function testTambahTahunAjaranKosong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tahun ajaran wajib diisi.');
        $this->service->tambah($this->input(['tahun_ajaran' => '   ']));
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
            $this->assertStringContainsString('Kegiatan wajib diisi.', $e->getMessage());
            $this->assertStringContainsString('Tanggal mulai wajib diisi.', $e->getMessage());
            $this->assertStringContainsString('Semester harus Ganjil atau Genap.', $e->getMessage());
            $this->assertStringContainsString('Tahun ajaran wajib diisi.', $e->getMessage());
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
        $this->model->method('errors')->willReturn(['kegiatan' => 'Kegiatan bermasalah.']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Kegiatan bermasalah.');
        $this->service->tambah($this->input());
    }

    // ---------- ubah ----------

    public function testUbahBerhasil(): void
    {
        $this->model->method('find')->willReturn(['id' => 3]);
        $this->model->expects($this->once())->method('update')
            ->with(3, $this->callback(static fn (array $d): bool => $d['kegiatan'] === 'Ujian Tengah Semester'))
            ->willReturn(true);

        $this->service->ubah(3, $this->input());
    }

    public function testUbahDataTidakAda(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(KalenderAkademikService::TIDAK_DITEMUKAN);
        $this->service->ubah(3, $this->input());
    }

    public function testUbahInputTidakValid(): void
    {
        $this->model->method('find')->willReturn(['id' => 3]);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(3, $this->input(['tanggal_selesai' => '2026-01-01']));
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