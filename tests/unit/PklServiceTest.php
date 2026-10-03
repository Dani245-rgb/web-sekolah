<?php

namespace Tests\Unit;

use App\Models\GuruModel;
use App\Models\PklPenempatanModel;
use App\Models\SiswaModel;
use App\Services\PklService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class PklServiceTest extends CIUnitTestCase
{
    private $model;
    private $siswaModel;
    private $guruModel;
    private PklService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model      = $this->createMock(PklPenempatanModel::class);
        $this->siswaModel = $this->createMock(SiswaModel::class);
        $this->guruModel  = $this->createMock(GuruModel::class);

        $this->service = new PklService($this->model, $this->siswaModel, $this->guruModel);
    }

    private function input(array $timpa = []): array
    {
        return array_merge([
            'id_siswa'                  => '4',
            'id_guru_pembimbing'        => '',
            'nama_perusahaan'           => '  PT   Maju  Jaya ',
            'alamat_perusahaan'         => "Jl. Industri\nNo. 5",
            'nama_pembimbing_industri'  => ' Pak   Budi ',
            'no_hp_pembimbing_industri' => ' 0812   345 ',
            'tanggal_mulai'             => '2026-07-01',
            'tanggal_selesai'           => '',
            'status'                    => 'Aktif',
            'catatan'                   => null,
        ], $timpa);
    }

    // ================= Daftar & form =================

    public function testDaftarFilterTidakValidDiabaikan(): void
    {
        $this->model->expects($this->once())
            ->method('getAllWithRelasi')
            ->with(['keyword' => null, 'status' => null])
            ->willReturn([]);

        $hasil = $this->service->daftar('   ', 'Ngawur');

        $this->assertSame(['keyword' => null, 'status' => null], $hasil['filter']);
    }

    public function testDaftarFilterValidDiteruskan(): void
    {
        $this->model->expects($this->once())
            ->method('getAllWithRelasi')
            ->with(['keyword' => 'maju', 'status' => 'Aktif'])
            ->willReturn([]);

        $this->service->daftar(' maju ', 'Aktif');
    }

    public function testDataFormMemuatSiswaGuruAktifDanPenempatan(): void
    {
        $this->siswaModel->method('getAll')->willReturnSelf();
        $this->siswaModel->method('findAll')->willReturn([['id_siswa' => 4]]);
        $this->guruModel->method('getAktif')->willReturn([['id_guru' => 2]]);

        $hasil = $this->service->dataForm(['id_penempatan' => 9]);

        $this->assertSame([['id_siswa' => 4]], $hasil['siswaList']);
        $this->assertSame([['id_guru' => 2]], $hasil['guruList']);
        $this->assertSame(['id_penempatan' => 9], $hasil['penempatan']);
    }

    // ================= Tambah =================

    public function testTambahValidasiGagalPesanMemuatSemuaError(): void
    {
        $this->model->method('validate')->willReturn(false);
        $this->model->method('errors')->willReturn([
            'nama_perusahaan' => 'Nama perusahaan wajib diisi.',
            'tanggal_mulai'   => 'Tanggal mulai wajib diisi.',
        ]);
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Nama perusahaan wajib diisi.; Tanggal mulai wajib diisi.');
        $this->service->tambah($this->input());
    }

    public function testTambahAturanBisnisDilanggarDitolak(): void
    {
        $this->model->method('validate')->willReturn(true);
        $this->model->method('cekAturan')->willReturn('Siswa ini sudah punya penempatan PKL yang berstatus Aktif.');
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Siswa ini sudah punya penempatan PKL yang berstatus Aktif.');
        $this->service->tambah($this->input());
    }

    public function testTambahBerhasilDataDirapikanDanKolomKosongJadiNull(): void
    {
        $this->model->method('validate')->willReturn(true);
        $this->model->method('cekAturan')->willReturn(null);
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['nama_perusahaan'] === 'PT Maju Jaya'
                    && $d['nama_pembimbing_industri'] === 'Pak Budi'
                    && $d['no_hp_pembimbing_industri'] === '0812 345'
                    && $d['alamat_perusahaan'] === "Jl. Industri\nNo. 5"
                    && $d['id_guru_pembimbing'] === null
                    && $d['tanggal_selesai'] === null
            ))
            ->willReturn(1);

        $this->service->tambah($this->input());
    }

    public function testTambahCekAturanTanpaKecualiId(): void
    {
        $this->model->method('validate')->willReturn(true);
        $this->model->expects($this->once())->method('cekAturan')->with($this->anything(), null)->willReturn(null);
        $this->model->method('insert')->willReturn(1);

        $this->service->tambah($this->input());
    }

    public function testTambahInsertGagalPesanTidakKosong(): void
    {
        $this->model->method('validate')->willReturn(true);
        $this->model->method('cekAturan')->willReturn(null);
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gagal menyimpan penempatan PKL.');
        $this->service->tambah($this->input());
    }

    // ================= Ubah =================

    public function testUbahDataTidakAdaDitolak(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(99, $this->input());
    }

    public function testUbahCekAturanMengecualikanPenempatanItuSendiri(): void
    {
        $this->model->method('find')->willReturn(['id_penempatan' => 3]);
        $this->model->method('validate')->willReturn(true);
        $this->model->expects($this->once())->method('cekAturan')->with($this->anything(), 3)->willReturn(null);
        $this->model->method('update')->willReturn(true);

        $this->service->ubah(3, $this->input());
    }

    public function testUbahBerhasilMemanggilUpdateDenganId(): void
    {
        $this->model->method('find')->willReturn(['id_penempatan' => 3]);
        $this->model->method('validate')->willReturn(true);
        $this->model->method('cekAturan')->willReturn(null);
        $this->model->expects($this->once())
            ->method('update')
            ->with(3, $this->callback(static fn (array $d) => $d['nama_perusahaan'] === 'PT Maju Jaya'))
            ->willReturn(true);

        $this->service->ubah(3, $this->input());
    }

    public function testUbahAturanBisnisDilanggarTidakMemperbarui(): void
    {
        $this->model->method('find')->willReturn(['id_penempatan' => 3]);
        $this->model->method('validate')->willReturn(true);
        $this->model->method('cekAturan')->willReturn('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(3, $this->input());
    }

    public function testUbahUpdateGagalPesanTidakKosong(): void
    {
        $this->model->method('find')->willReturn(['id_penempatan' => 3]);
        $this->model->method('validate')->willReturn(true);
        $this->model->method('cekAturan')->willReturn(null);
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gagal memperbarui penempatan PKL.');
        $this->service->ubah(3, $this->input());
    }

    // ================= Hapus =================

    public function testHapusMemanggilDelete(): void
    {
        $this->model->method('find')->willReturn(['id_penempatan' => 3]);
        $this->model->expects($this->once())->method('delete')->with(3);

        $this->service->hapus(3);
    }

    public function testHapusDataTidakAdaDitolak(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('delete');

        $this->expectException(InvalidArgumentException::class);
        $this->service->hapus(99);
    }
}