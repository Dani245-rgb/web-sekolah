<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\Mock\MockCache;
use Config\Database;
use Config\Services;

/**
 * Feature test form Kontak publik: POST kontak/kirim sampai data benar-benar
 * tersimpan di database TES (web_sekolah_test), bukan database asli.
 *
 * Struktur tabel dimuat dari writable/schema_tes.sql, jadi migration dimatikan.
 *
 * @internal
 */
final class KontakFormTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $DBGroup   = 'tests';
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pastikanDatabaseTes();

        // Counter rate-limit (throttle) disimpan di cache. Pakai cache in-memory yang baru
        // di setiap tes: penghitung mulai dari nol dan file cache aplikasi tidak tersentuh.
        Services::injectMock('cache', new MockCache());
        Services::resetSingle('throttler');

        $this->db->table('pesan_kontak')->emptyTable();
        $this->db->table('notifikasi')->emptyTable();
    }

    /**
     * PENGAMAN: berhenti sebelum menulis apa pun kalau aplikasi tidak memakai database tes.
     */
    private function pastikanDatabaseTes(): void
    {
        $this->assertSame('testing', ENVIRONMENT, 'ENVIRONMENT harus "testing" saat PHPUnit jalan.');
        $this->assertSame('tests', config('Database')->defaultGroup, 'Grup default harus "tests".');
        $this->assertSame('web_sekolah_test', Database::connect()->getDatabase(), 'Aplikasi harus memakai web_sekolah_test.');
        $this->assertSame('web_sekolah_test', $this->db->getDatabase(), 'Koneksi tes harus web_sekolah_test.');
    }

    /**
     * Tambahkan token CSRF yang sah, seperti yang dikirim form di browser.
     */
    private function denganCsrf(array $data): array
    {
        helper('form');

        return [csrf_token() => csrf_hash()] + $data;
    }

    public function testKirimPesanValidTersimpanDanAdminDinotifikasi(): void
    {
        $result = $this->post('kontak/kirim', $this->denganCsrf([
            'nama'   => 'Budi Santoso',
            'email'  => 'budi@example.com',
            'subjek' => 'Tanya jadwal',
            'pesan'  => 'Kapan jadwal pendaftaran dibuka?',
        ]));

        $result->assertRedirectTo('/kontak');
        $result->assertSessionHas('success', 'Pesan Anda berhasil dikirim. Terima kasih!');

        $this->seeInDatabase('pesan_kontak', [
            'nama'   => 'Budi Santoso',
            'email'  => 'budi@example.com',
            'subjek' => 'Tanya jadwal',
            'pesan'  => 'Kapan jadwal pendaftaran dibuka?',
        ]);
        $this->seeNumRecords(1, 'notifikasi', []);
    }

    public function testSubjekBolehKosong(): void
    {
        $result = $this->post('kontak/kirim', $this->denganCsrf([
            'nama'   => 'Siti Aminah',
            'email'  => 'siti@example.com',
            'subjek' => '',
            'pesan'  => 'Halo, mau tanya.',
        ]));

        $result->assertRedirectTo('/kontak');
        $this->seeNumRecords(1, 'pesan_kontak', ['nama' => 'Siti Aminah', 'subjek' => '']);
        $this->seeNumRecords(1, 'notifikasi', []);
    }

    public function testEmailTidakValidDitolakDanTidakTersimpan(): void
    {
        $result = $this->post('kontak/kirim', $this->denganCsrf([
            'nama'   => 'Budi Santoso',
            'email'  => 'bukan-email',
            'subjek' => 'Tes',
            'pesan'  => 'Isi pesan.',
        ]));

        $result->assertRedirect();
        $result->assertSessionHas('errors');

        $errors = session('errors');
        $this->assertIsArray($errors);
        $this->assertArrayNotHasKey('throttle', $errors, 'Ditolak karena rate-limit, bukan karena validasi.');
        $this->assertArrayNotHasKey('limit', $errors, 'Ditolak karena rate-limit, bukan karena validasi.');
        $this->assertArrayHasKey('email', $errors);

        $this->seeNumRecords(0, 'pesan_kontak', []);
        $this->seeNumRecords(0, 'notifikasi', []);
    }
}
