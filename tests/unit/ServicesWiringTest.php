<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Memastikan semua service terdaftar di Config/Services.php dan bisa dibuat
 * dengan argumen konstruktor yang benar.
 *
 * @internal
 */
final class ServicesWiringTest extends CIUnitTestCase
{
    #[DataProvider('daftarService')]
    public function testServiceBisaDibuat(string $nama): void
    {
        $this->assertIsObject(service($nama, false), "Service '{$nama}' gagal dibuat.");
    }

    public static function daftarService(): array
    {
        $daftar = [
            'siswaService', 'fileUploadService', 'slugService', 'authService',
            'backupService', 'laporanSiswaService', 'dashboardService', 'berandaService',
            'kontakService', 'ppdbService', 'passwordService', 'jurusanService',
            'bkArtikelService', 'unduhanService', 'importSiswaService',
        ];

        return array_map(static fn (string $n): array => [$n], $daftar);
    }
}