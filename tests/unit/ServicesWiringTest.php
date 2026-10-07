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
        $daftar = [];

        foreach ((new \ReflectionClass(\Config\Services::class))->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $metode) {
            if ($metode->getDeclaringClass()->getName() === \Config\Services::class) {
                $daftar[$metode->getName()] = [$metode->getName()];
            }
        }

        return $daftar;
    }

    public function testSemuaFileServiceTerdaftar(): void
    {
        $belumTerdaftar = [];

        foreach (glob(APPPATH . 'Services/*Service.php') ?: [] as $file) {
            $nama = lcfirst(basename($file, '.php'));

            if (!method_exists(\Config\Services::class, $nama)) {
                $belumTerdaftar[] = $nama;
            }
        }

        $this->assertSame([], $belumTerdaftar, 'File service belum didaftarkan di Config/Services.php: ' . implode(', ', $belumTerdaftar));
    }
}