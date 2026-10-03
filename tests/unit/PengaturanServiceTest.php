<?php

namespace Tests\Unit;

use App\Models\AuditLogModel;
use App\Models\PengaturanModel;
use App\Services\FileUploadService;
use App\Services\PengaturanService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class PengaturanServiceTest extends CIUnitTestCase
{
    private const FOLDER = 'assets/uploads/sekolah';

    private $model;
    private $audit;
    private $uploader;
    private PengaturanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model    = $this->createMock(PengaturanModel::class);
        $this->audit    = $this->createMock(AuditLogModel::class);
        $this->uploader = $this->createMock(FileUploadService::class);

        $this->service = new PengaturanService($this->model, $this->audit, $this->uploader);
    }

    private function input(): array
    {
        return [
            'nama_sekolah' => '  SMK   Attaufiqiyyah ',
            'alamat'       => "Jl. Contoh\nNo. 1",
            'telepon'      => ' 021   123 ',
            'email'        => 'info@smk.sch.id',
        ];
    }

    private function pengaturanLama(): array
    {
        return ['id' => 1, 'logo' => 'logo-lama.png', 'favicon' => 'fav-lama.png'];
    }

    /**
     * Uploader palsu: panggilan pertama (logo) dan kedua (favicon) mengembalikan nilai berurutan.
     */
    private function uploadBerurutan(?string $logo, ?string $favicon): void
    {
        $this->uploader->method('simpan')->willReturnOnConsecutiveCalls($logo, $favicon);
    }

    public function testSimpanDataPengaturanBelumAdaDitolak(): void
    {
        $this->model->method('getPengaturan')->willReturn(null);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Data pengaturan belum ada di database.');
        $this->service->simpan($this->input(), null, null, 1, 'admin');
    }

    public function testSimpanBerhasilTeksDirapikanDanAuditDicatat(): void
    {
        $this->model->method('getPengaturan')->willReturn($this->pengaturanLama());
        $this->uploadBerurutan(null, null);
        $this->model->expects($this->once())
            ->method('update')
            ->with(1, $this->callback(
                static fn (array $d) => $d['nama_sekolah'] === 'SMK Attaufiqiyyah'
                    && $d['telepon'] === '021 123'
                    && $d['alamat'] === "Jl. Contoh\nNo. 1"
                    && !array_key_exists('logo', $d)
                    && !array_key_exists('favicon', $d)
            ))
            ->willReturn(true);

        $this->audit->expects($this->once())
            ->method('catat')
            ->with(7, 'admin', 'update_pengaturan', 'Mengubah pengaturan identitas sekolah.');

        $this->uploader->expects($this->never())->method('hapus');

        $this->service->simpan($this->input(), null, null, 7, 'admin');
    }

    public function testLogoDitolakUploaderTidakMenyimpanDanPesanMenyebutLogo(): void
    {
        $this->model->method('getPengaturan')->willReturn($this->pengaturanLama());
        $this->uploader->method('simpan')->willThrowException(new InvalidArgumentException('Tipe file tidak diizinkan.'));
        $this->model->expects($this->never())->method('update');
        $this->audit->expects($this->never())->method('catat');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Logo: Tipe file tidak diizinkan.');
        $this->service->simpan($this->input(), null, null, 1, 'admin');
    }

    public function testFaviconDitolakLogoBaruDibuang(): void
    {
        $this->model->method('getPengaturan')->willReturn($this->pengaturanLama());

        $panggilan = 0;
        $this->uploader->method('simpan')->willReturnCallback(
            static function () use (&$panggilan) {
                $panggilan++;
                if ($panggilan === 1) {
                    return 'logo-baru.png';
                }

                throw new InvalidArgumentException('Ukuran file terlalu besar.');
            }
        );

        $this->uploader->expects($this->once())->method('hapus')->with('logo-baru.png', self::FOLDER);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Favicon: Ukuran file terlalu besar.');
        $this->service->simpan($this->input(), null, null, 1, 'admin');
    }

    public function testUpdateGagalKeduaFileBaruDibuangFileLamaAman(): void
    {
        $this->model->method('getPengaturan')->willReturn($this->pengaturanLama());
        $this->uploadBerurutan('logo-baru.png', 'fav-baru.png');
        $this->model->method('update')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $dihapus = [];
        $this->uploader->method('hapus')->willReturnCallback(
            static function ($nama, $folder) use (&$dihapus) {
                $dihapus[] = [$nama, $folder];
            }
        );
        $this->audit->expects($this->never())->method('catat');

        try {
            $this->service->simpan($this->input(), null, null, 1, 'admin');
            $this->fail('Seharusnya melempar InvalidArgumentException.');
        } catch (InvalidArgumentException $e) {
            $this->assertSame('Gagal menyimpan pengaturan.', $e->getMessage());
        }

        $this->assertSame([['logo-baru.png', self::FOLDER], ['fav-baru.png', self::FOLDER]], $dihapus);
    }

    public function testSimpanBerhasilFileLamaDihapusSetelahUpdate(): void
    {
        $this->model->method('getPengaturan')->willReturn($this->pengaturanLama());
        $this->uploadBerurutan('logo-baru.png', 'fav-baru.png');
        $this->model->method('update')->willReturn(true);

        $dihapus = [];
        $this->uploader->method('hapus')->willReturnCallback(
            static function ($nama, $folder) use (&$dihapus) {
                $dihapus[] = [$nama, $folder];
            }
        );

        $this->service->simpan($this->input(), null, null, 1, 'admin');

        $this->assertSame([['logo-lama.png', self::FOLDER], ['fav-lama.png', self::FOLDER]], $dihapus);
    }

    public function testHanyaLogoBaruFaviconLamaTidakDisentuh(): void
    {
        $this->model->method('getPengaturan')->willReturn($this->pengaturanLama());
        $this->uploadBerurutan('logo-baru.png', null);
        $this->model->method('update')->willReturn(true);

        $this->uploader->expects($this->once())->method('hapus')->with('logo-lama.png', self::FOLDER);

        $this->service->simpan($this->input(), null, null, 1, 'admin');
    }
}