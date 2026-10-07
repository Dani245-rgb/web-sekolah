<?php

namespace Tests\Unit;

use App\Models\NotifikasiModel;
use App\Services\NotifikasiService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @internal
 */
final class NotifikasiServiceTest extends CIUnitTestCase
{
    private const BASE = 'https://sekolah.test/';

    private NotifikasiModel&MockObject $model;
    private NotifikasiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model   = $this->createMock(NotifikasiModel::class);
        $this->service = new NotifikasiService($this->model, self::BASE);
    }

    // ---------- daftar / ringkasan ----------

    public function testDaftarMemakaiLimitDefault50(): void
    {
        $this->model->expects($this->once())->method('getForUser')->with(3, 50)->willReturn([['id_notif' => 1]]);

        $this->assertCount(1, $this->service->daftar(3));
    }

    public function testRingkasanBerisiUnreadDanLimaTerbaru(): void
    {
        $this->model->method('countUnread')->with(3)->willReturn(4);
        $this->model->expects($this->once())->method('getForUser')->with(3, 5)->willReturn([['id_notif' => 9]]);

        $hasil = $this->service->ringkasan(3);

        $this->assertSame(4, $hasil['unread_count']);
        $this->assertSame([['id_notif' => 9]], $hasil['terbaru']);
    }

    // ---------- baca ----------

    public function testBacaTidakDitemukanMelempar(): void
    {
        $this->model->method('cariUntukUser')->with(7, 3)->willReturn(null);
        $this->model->expects($this->never())->method('tandaiDibaca');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(NotifikasiService::TIDAK_DITEMUKAN);
        $this->service->baca(7, 3);
    }

    public function testBacaMenandaiDibacaDanMengembalikanLinkRelatif(): void
    {
        $this->model->method('cariUntukUser')->willReturn(['link' => '/admin/ppdb/detail/5']);
        $this->model->expects($this->once())->method('tandaiDibaca')->with(7);

        $this->assertSame('/admin/ppdb/detail/5', $this->service->baca(7, 3));
    }

    public function testBacaLinkRelatifTanpaGarisMiringDiAwalBoleh(): void
    {
        $this->model->method('cariUntukUser')->willReturn(['link' => 'admin/kontak/2']);

        $this->assertSame('admin/kontak/2', $this->service->baca(7, 3));
    }

    public function testBacaLinkAlamatSitusSendiriBoleh(): void
    {
        $this->model->method('cariUntukUser')->willReturn(['link' => self::BASE . 'admin/kontak/2']);

        $this->assertSame(self::BASE . 'admin/kontak/2', $this->service->baca(7, 3));
    }

    #[DataProvider('linkTidakAman')]
    public function testBacaLinkLuarDitolakTapiTetapDitandaiDibaca(string $link): void
    {
        $this->model->method('cariUntukUser')->willReturn(['link' => $link]);
        $this->model->expects($this->once())->method('tandaiDibaca')->with(7);

        $this->assertNull($this->service->baca(7, 3));
    }

    public static function linkTidakAman(): array
    {
        return [
            'situs lain'      => ['https://situs-lain.com/x'],
            'protocol relatif' => ['//situs-lain.com/x'],
            'javascript'      => ['javascript:alert(1)'],
            'http situs lain' => ['http://sekolah.test.evil.com/'],
        ];
    }

    public function testBacaTanpaLinkMengembalikanNull(): void
    {
        $this->model->method('cariUntukUser')->willReturn(['link' => null]);
        $this->model->expects($this->once())->method('tandaiDibaca');

        $this->assertNull($this->service->baca(7, 3));
    }

    public function testBacaLinkKosongMengembalikanNull(): void
    {
        $this->model->method('cariUntukUser')->willReturn(['link' => '']);

        $this->assertNull($this->service->baca(7, 3));
    }

    // ---------- bacaSemua ----------

    public function testBacaSemuaBerhasil(): void
    {
        $this->model->expects($this->once())->method('tandaiSemuaDibaca')->with(3)->willReturn(true);

        $this->service->bacaSemua(3);
    }

    public function testBacaSemuaGagalJadiRuntime(): void
    {
        $this->model->method('tandaiSemuaDibaca')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->service->bacaSemua(3);
    }
}