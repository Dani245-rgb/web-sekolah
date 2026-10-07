<?php

namespace Tests\Unit;

use App\Models\AuditLogModel;
use App\Services\AuditLogService;
use CodeIgniter\Pager\Pager;
use CodeIgniter\Test\CIUnitTestCase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
final class AuditLogServiceTest extends CIUnitTestCase
{
    private AuditLogModel&MockObject $model;
    private AuditLogService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model   = $this->createMock(AuditLogModel::class);
        $this->service = new AuditLogService($this->model);
    }

    // ---------- filterDariInput ----------

    public function testFilterInputKosongSemuaNull(): void
    {
        $this->assertSame(
            ['aksi' => null, 'username' => null, 'dari' => null, 'sampai' => null],
            $this->service->filterDariInput([])
        );
    }

    public function testFilterTeksDitrimDanKosongJadiNull(): void
    {
        $hasil = $this->service->filterDariInput(['aksi' => '  Login ', 'username' => '   ']);

        $this->assertSame('Login', $hasil['aksi']);
        $this->assertNull($hasil['username']);
    }

    public function testFilterNilaiBukanStringDianggapTidakAda(): void
    {
        $hasil = $this->service->filterDariInput(['aksi' => ['x'], 'username' => 123, 'dari' => ['2026-01-01']]);

        $this->assertNull($hasil['aksi']);
        $this->assertNull($hasil['username']);
        $this->assertNull($hasil['dari']);
    }

    public function testFilterTanggalValidDipertahankan(): void
    {
        $hasil = $this->service->filterDariInput(['dari' => '2026-10-01', 'sampai' => ' 2026-10-06 ']);

        $this->assertSame('2026-10-01', $hasil['dari']);
        $this->assertSame('2026-10-06', $hasil['sampai']);
    }

    #[DataProvider('tanggalTidakValid')]
    public function testFilterTanggalTidakValidJadiNull(string $nilai): void
    {
        $this->assertNull($this->service->filterDariInput(['dari' => $nilai])['dari']);
    }

    public static function tanggalTidakValid(): array
    {
        return [
            'bulan 13'          => ['2026-13-01'],
            'hari tidak ada'    => ['2026-02-30'],
            'tanpa nol di depan' => ['2026-2-3'],
            'format lain'       => ['06/10/2026'],
            'teks'              => ['kemarin'],
        ];
    }

    // ---------- halaman / semua ----------

    public function testHalamanMengembalikanLogPagerDanDaftarAksi(): void
    {
        $filter = ['aksi' => 'Login', 'username' => null, 'dari' => null, 'sampai' => null];
        $pager  = $this->createMock(Pager::class);

        $this->model->pager = $pager;
        $this->model->expects($this->once())->method('filterQuery')->with($filter)->willReturnSelf();
        $this->model->expects($this->once())->method('paginate')->with(30)->willReturn([['id_log' => 1]]);
        $this->model->method('daftarAksi')->willReturn(['Login', 'Logout']);

        $hasil = $this->service->halaman($filter);

        $this->assertSame([['id_log' => 1]], $hasil['logs']);
        $this->assertSame($pager, $hasil['pager']);
        $this->assertSame(['Login', 'Logout'], $hasil['daftarAksi']);
    }

    public function testHalamanMemakaiJumlahPerHalamanYangDiminta(): void
    {
        $this->model->method('filterQuery')->willReturnSelf();
        $this->model->expects($this->once())->method('paginate')->with(10)->willReturn([]);
        $this->model->method('daftarAksi')->willReturn([]);

        $this->service->halaman([], 10);
    }

    public function testSemuaMemakaiFilterYangSama(): void
    {
        $filter = ['aksi' => null, 'username' => 'budi', 'dari' => null, 'sampai' => null];

        $this->model->expects($this->once())->method('filterQuery')->with($filter)->willReturnSelf();
        $this->model->expects($this->once())->method('findAll')->willReturn([['id_log' => 1], ['id_log' => 2]]);

        $this->assertCount(2, $this->service->semua($filter));
    }

    // ---------- buatExcel ----------

    public function testBuatExcelMenghasilkanFileXlsxDenganIsiBenar(): void
    {
        $logs = [
            [
                'created_at' => '2026-10-06 10:00:00',
                'username'   => 'budi',
                'aksi'       => 'Login',
                'keterangan' => '=HYPERLINK("http://x","klik")',
                'ip_address' => '127.0.0.1',
            ],
            [
                'created_at' => '2026-10-06 11:00:00',
                'username'   => null,
                'aksi'       => 'Hapus',
                'keterangan' => null,
                'ip_address' => null,
            ],
        ];

        $isi = $this->service->buatExcel($logs);

        $this->assertSame('PK', substr($isi, 0, 2), 'File xlsx adalah arsip zip.');

        $tmp = tempnam(sys_get_temp_dir(), 'audit');
        file_put_contents($tmp, $isi);

        try {
            $sheet = (new XlsxReader())->load($tmp)->getActiveSheet();
        } finally {
            unlink($tmp);
        }

        $this->assertSame('Audit Log', $sheet->getTitle());
        $this->assertSame('No', $sheet->getCell('A1')->getValue());
        $this->assertSame('IP Address', $sheet->getCell('F1')->getValue());
        $this->assertSame(3, $sheet->getHighestRow());

        $this->assertEquals(1, $sheet->getCell('A2')->getValue());
        $this->assertSame('2026-10-06 10:00:00', $sheet->getCell('B2')->getValue());
        $this->assertSame('Login', $sheet->getCell('D2')->getValue());
        $this->assertStringStartsNotWith('=', (string) $sheet->getCell('E2')->getValue(), 'Rumus harus disanitasi.');
        $this->assertSame('127.0.0.1', $sheet->getCell('F2')->getValue());

        $this->assertEquals(2, $sheet->getCell('A3')->getValue());
        $this->assertSame('Hapus', $sheet->getCell('D3')->getValue());
        $this->assertSame('-', $sheet->getCell('F3')->getValue());
    }

    public function testBuatExcelTanpaLogTetapMenghasilkanHeader(): void
    {
        $isi = $this->service->buatExcel([]);

        $this->assertSame('PK', substr($isi, 0, 2));
    }
}