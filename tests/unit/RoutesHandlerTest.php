<?php

namespace Tests\Unit;

use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use PHPUnit\Framework\Attributes\DataProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionMethod;

/**
 * Cek "kabel" aplikasi tanpa DB:
 * 1. Setiap route menunjuk ke controller + method public yang benar-benar ada,
 *    dan jumlah parameter URL ($1, $2) cocok dengan parameter method.
 * 2. Setiap service('nama') yang dipanggil di controller terdaftar di Config\Services.
 *
 * @internal
 */
final class RoutesHandlerTest extends CIUnitTestCase
{
    private static ?array $cache = null;

    /**
     * @return array<string, array{0: string, 1: string}> label => [route, handler]
     */
    private static function semuaRoute(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        // Instance baru (bukan yang shared) supaya tidak terpengaruh state bootstrap tes.
        $routes = new RouteCollection(service('locator'), config('Modules'), config('Routing'));
        $routes->loadRoutes();

        $hasil = [];

        // CI 4.7 memakai nama verb huruf besar.
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $verb) {
            foreach ($routes->getRoutes($verb) as $route => $handler) {
                if (! is_string($handler)) {
                    continue; // closure: tidak bisa dicek lewat nama
                }

                $hasil[$verb . ' ' . $route] = [(string) $route, $handler];
            }
        }

        if ($hasil === []) {
            // Biar gagalnya jelas, bukan "empty data set".
            $hasil = ['TIDAK ADA ROUTE TERBACA' => ['', '']];
        }

        return self::$cache = $hasil;
    }

    public static function daftarRoute(): array
    {
        return self::semuaRoute();
    }

    #[DataProvider('daftarRoute')]
    public function testRouteMenunjukKeMethodYangAda(string $route, string $handler): void
    {
        if ($route === '' && $handler === '') {
            $this->fail('Tidak ada route yang terbaca dari app/Config/Routes.php.');
        }

        $bagian = explode('::', ltrim($handler, '\\'), 2);
        $kelas  = $bagian[0];
        $sisa   = $bagian[1] ?? 'index';
        $method = strpos($sisa, '/') !== false ? strstr($sisa, '/', true) : $sisa;

        $this->assertTrue(class_exists($kelas), "Controller tidak ada: {$kelas} (route {$route})");
        $this->assertTrue(method_exists($kelas, $method), "Method tidak ada: {$kelas}::{$method} (route {$route})");

        $ref = new ReflectionMethod($kelas, $method);
        $this->assertTrue($ref->isPublic() && ! $ref->isStatic(), "{$kelas}::{$method} harus public non-static");

        $placeholder = preg_match_all('/\$\d+/', $sisa);
        $wajib       = $ref->getNumberOfRequiredParameters();
        $total       = $ref->isVariadic() ? PHP_INT_MAX : $ref->getNumberOfParameters();

        $this->assertGreaterThanOrEqual($wajib, $placeholder, "Parameter URL kurang untuk {$kelas}::{$method}");
        $this->assertLessThanOrEqual($total, $placeholder, "Parameter URL kelebihan untuk {$kelas}::{$method}");
    }

    public function testSemuaServiceYangDipanggilControllerTerdaftar(): void
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . 'Controllers'));
        $hilang   = [];
        $diperiksa = 0;

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $kode = file_get_contents($file->getPathname());

            if (preg_match_all('/\bservice\(\s*[\'"]([A-Za-z0-9_]+)[\'"]/', $kode, $cocok)) {
                foreach ($cocok[1] as $nama) {
                    $diperiksa++;

                    // Service bawaan CI (session, request, dst.) ada di CoreServices,
                    // service buatan kita ada di Config\Services.
                    if (! method_exists(Services::class, $nama) && ! method_exists(CoreServices::class, $nama)) {
                        $hilang[] = $file->getFilename() . ' -> service(\'' . $nama . '\')';
                    }
                }
            }
        }

        $this->assertGreaterThan(0, $diperiksa, 'Tidak ada pemanggilan service() yang ditemukan, cek path.');
        $this->assertSame([], $hilang, "Service belum terdaftar di Config\\Services:\n" . implode("\n", $hilang));
    }
}
