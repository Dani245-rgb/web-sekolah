<?php

namespace Tests\Unit;

use App\Services\BackupService;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Menguji bagian BackupService yang tidak butuh mysqldump: daftar, path, dan hapus.
 * buat() tidak dites di sini karena memanggil exec() dan mysqldump sungguhan.
 *
 * @internal
 */
final class BackupServiceTest extends CIUnitTestCase
{
    private string $folder;
    private BackupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_test_' . uniqid('', true);
        mkdir($this->folder, 0755, true);

        $this->service = new BackupService($this->folder);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->folder . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($this->folder);

        parent::tearDown();
    }

    private function buatFile(string $nama, int $ukuranByte = 10, ?int $waktu = null): string
    {
        $path = $this->folder . DIRECTORY_SEPARATOR . $nama;
        file_put_contents($path, str_repeat('a', $ukuranByte));

        if ($waktu !== null) {
            touch($path, $waktu);
        }

        return $path;
    }

    // ---------- daftar ----------

    public function testDaftarFolderKosong(): void
    {
        $this->assertSame([], $this->service->daftar());
    }

    public function testDaftarHanyaFileSqlDanTerbaruDuluan(): void
    {
        $waktu = strtotime('2026-10-06 10:30:00');

        $this->buatFile('backup_db_2026-10-01_080000.sql', 2048, $waktu);
        $this->buatFile('backup_db_2026-10-02_080000.sql', 10, $waktu);
        $this->buatFile('catatan.txt');

        $daftar = $this->service->daftar();

        $this->assertCount(2, $daftar);
        $this->assertSame('backup_db_2026-10-02_080000.sql', $daftar[0]['nama']);
        $this->assertSame('backup_db_2026-10-01_080000.sql', $daftar[1]['nama']);
        $this->assertSame('2 KB', $daftar[1]['ukuran']);
        $this->assertSame(date('d M Y H:i', $waktu), $daftar[1]['tanggal']);
    }

    // ---------- path ----------

    public function testPathFileAdaMengembalikanPathLengkap(): void
    {
        $this->buatFile('backup_db.sql');

        $this->assertSame($this->folder . DIRECTORY_SEPARATOR . 'backup_db.sql', $this->service->path('backup_db.sql'));
    }

    public function testPathFileTidakAdaMengembalikanNull(): void
    {
        $this->assertNull($this->service->path('tidak_ada.sql'));
    }

    public function testPathTraversalDipotongKeNamaFileDiDalamFolder(): void
    {
        $this->buatFile('backup_db.sql');

        $this->assertSame(
            $this->folder . DIRECTORY_SEPARATOR . 'backup_db.sql',
            $this->service->path('../../backup_db.sql')
        );
    }

    #[DataProvider('namaTidakValid')]
    public function testPathNamaTidakValidMengembalikanNull(string $nama): void
    {
        $this->buatFile('passwd');
        $this->buatFile('catatan.txt');

        $this->assertNull($this->service->path($nama));
    }

    public static function namaTidakValid(): array
    {
        return [
            'bukan sql'        => ['catatan.txt'],
            'tanpa ekstensi'   => ['passwd'],
            'traversal ke file lain' => ['../../etc/passwd'],
            'dua titik'        => ['a..sql'],
            'garis miring terbalik' => ['..\\..\\x.sql'],
            'ada spasi'        => ['backup db.sql'],
            'kosong'           => [''],
        ];
    }

    public function testFolderDenganGarisMiringDiAkhirTetapBekerja(): void
    {
        $service = new BackupService($this->folder . '/');
        $this->buatFile('backup_db.sql');

        $this->assertSame($this->folder . DIRECTORY_SEPARATOR . 'backup_db.sql', $service->path('backup_db.sql'));
    }

    // ---------- hapus ----------

    public function testHapusFileAdaBerhasil(): void
    {
        $path = $this->buatFile('backup_db.sql');

        $this->assertTrue($this->service->hapus('backup_db.sql'));
        $this->assertFileDoesNotExist($path);
    }

    public function testHapusFileTidakAdaMengembalikanFalse(): void
    {
        $this->assertFalse($this->service->hapus('tidak_ada.sql'));
    }

    public function testHapusNamaTidakValidTidakMenghapusApaPun(): void
    {
        $path = $this->buatFile('catatan.txt');

        $this->assertFalse($this->service->hapus('catatan.txt'));
        $this->assertFileExists($path);
    }
}