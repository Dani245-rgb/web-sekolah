<?php

namespace Tests\Unit;

use App\Models\GuruModel;
use App\Services\FileUploadService;
use App\Services\GuruService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class GuruServiceTest extends CIUnitTestCase
{
    private const FOLDER = 'guru';

    private $model;
    private $uploader;
    private GuruService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(GuruModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);

        $this->service = new GuruService($this->model, $this->uploader);
    }

    private function guruLama(): array
    {
        return ['id_guru' => 1, 'nama' => 'Budi', 'foto' => 'lama.jpg'];
    }

    public function testRapikanInputKolomKosongJadiNullDanSpasiDirapikan(): void
    {
        $data = $this->service->rapikanInput([
            'nip'           => ' 1987 ',
            'nama'          => 'Budi    Santoso',
            'nuptk'         => '',
            'tanggal_lahir' => '',
            'email'         => '   ',
            'jenis_kelamin' => 'L',
        ]);

        $this->assertSame('Budi Santoso', $data['nama']);
        $this->assertSame('1987', $data['nip']);
        $this->assertNull($data['nuptk']);
        $this->assertNull($data['tanggal_lahir']);
        $this->assertNull($data['email']);
        $this->assertArrayNotHasKey('status', $data);
    }

    public function testRapikanInputMembawaStatusKalauDikirim(): void
    {
        $data = $this->service->rapikanInput(['nip' => '1', 'nama' => 'Budi', 'status' => 'Nonaktif']);

        $this->assertSame('Nonaktif', $data['status']);
    }

    public function testKeywordSpasiSajaDianggapKosong(): void
    {
        $this->model->expects($this->once())->method('cari')->with(null)->willReturnSelf();
        $this->model->method('paginate')->willReturn([]);

        $hasil = $this->service->daftar('   ');

        $this->assertNull($hasil['keyword']);
    }

    public function testTambahFotoDitolakTidakMenyimpan(): void
    {
        $this->uploader->method('simpanKompres')->willThrowException(new InvalidArgumentException('Tipe file tidak diizinkan.'));
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah(['nip' => '1', 'nama' => 'Budi'], null);
    }

    public function testTambahBerhasilStatusAktifDanFotoTersimpan(): void
    {
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['status'] === 'Aktif' && $d['foto'] === 'baru.jpg'
            ))
            ->willReturn(1);

        $this->service->tambah(['nip' => '1', 'nama' => 'Budi'], null);
    }

    public function testTambahGagalDbFotoBaruDibuang(): void
    {
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->service->tambah(['nip' => '1', 'nama' => 'Budi'], null);
    }

    public function testUbahGagalFotoLamaTidakDihapus(): void
    {
        $this->model->method('findWithUser')->willReturn($this->guruLama());
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(false);

        // Hanya foto BARU yang dibuang, foto lama aman
        $this->uploader->expects($this->once())->method('hapus')->with('baru.jpg', self::FOLDER);

        $this->expectException(InvalidArgumentException::class);
        $this->service->ubah(1, ['nama' => 'Budi'], null);
    }

    public function testUbahBerhasilFotoLamaDihapusSetelahUpdate(): void
    {
        $this->model->method('findWithUser')->willReturn($this->guruLama());
        $this->uploader->method('simpanKompres')->willReturn('baru.jpg');
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->once())->method('hapus')->with('lama.jpg', self::FOLDER);

        $this->service->ubah(1, ['nama' => 'Budi'], null);
    }

    public function testUbahTanpaFotoBaruTidakMenghapusApaPun(): void
    {
        $this->model->method('findWithUser')->willReturn($this->guruLama());
        $this->uploader->method('simpanKompres')->willReturn(null);
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->never())->method('hapus');

        $this->service->ubah(1, ['nama' => 'Budi'], null);
    }

    public function testNonaktifkanHanyaUbahStatus(): void
    {
        $this->model->method('findWithUser')->willReturn($this->guruLama());
        $this->model->expects($this->once())->method('update')->with(1, ['status' => 'Nonaktif'])->willReturn(true);
        $this->uploader->expects($this->never())->method('hapus');

        $this->service->nonaktifkan(1);
    }

    public function testNonaktifkanGuruTidakAdaDitolak(): void
    {
        $this->model->method('findWithUser')->willReturn(null);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->service->nonaktifkan(99);
    }
}