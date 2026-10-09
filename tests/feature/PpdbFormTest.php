<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\Mock\MockCache;
use Config\Database;
use Config\Services;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Feature test form PPDB publik: POST ppdb/daftar sampai data benar-benar
 * tersimpan di database TES (web_sekolah_test), bukan database asli.
 *
 * Struktur tabel dimuat dari writable/schema_tes.sql, jadi migration dimatikan.
 *
 * @internal
 */
final class PpdbFormTest extends CIUnitTestCase
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

        // PpdbService menyimpan pengaturan di memori dan service dipakai bareng antar tes.
        Services::resetSingle('ppdbService');

        foreach (['pendaftar_ppdb', 'pengaturan_ppdb', 'notifikasi', 'audit_log'] as $tabel) {
            $this->db->table($tabel)->emptyTable();
        }
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

    private function aturPendaftaran(string $status): void
    {
        $this->db->table('pengaturan_ppdb')->insert([
            'id'           => 1,
            'status'       => $status,
            'pesan_tutup'  => 'Pendaftaran belum dibuka.',
            'tahun_ajaran' => '2026/2027',
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Tambahkan token CSRF yang sah, seperti yang dikirim form di browser.
     */
    private function denganCsrf(array $data): array
    {
        helper('form');

        return [csrf_token() => csrf_hash()] + $data;
    }

    private static function dataValid(): array
    {
        return [
            'nama_lengkap'    => 'Budi Santoso',
            'tempat_lahir'    => 'Bandung',
            'tanggal_lahir'   => '2010-05-17',
            'jenis_kelamin'   => 'Laki-laki',
            'asal_sekolah'    => 'SMP Negeri 1',
            'jurusan_pilihan' => 'TKJ',
            'no_hp'           => '081234567890',
            'email'           => 'budi@example.com',
            'alamat'          => 'Jl. Merdeka No. 1',
        ];
    }

    public function testPendaftaranDibukaTersimpanDenganTahunAjaranDanAdminDinotifikasi(): void
    {
        $this->aturPendaftaran('buka');

        $result = $this->post('ppdb/daftar', $this->denganCsrf(self::dataValid()));

        $result->assertRedirectTo('/ppdb');
        $result->assertSessionHas('success', 'Pendaftaran berhasil dikirim! Kami akan menghubungi Anda segera.');

        $this->seeInDatabase('pendaftar_ppdb', [
            'nama_lengkap'    => 'Budi Santoso',
            'no_hp'           => '081234567890',
            'jurusan_pilihan' => 'TKJ',
            'tahun_ajaran'    => '2026/2027',
        ]);
        $this->seeNumRecords(1, 'notifikasi', []);
    }

    public function testPendaftaranDitutupDitolakDanTidakTersimpan(): void
    {
        $this->aturPendaftaran('tutup');

        $result = $this->post('ppdb/daftar', $this->denganCsrf(self::dataValid()));

        $result->assertRedirectTo('/ppdb');
        $result->assertSessionHas('error', 'Pendaftaran sedang ditutup.');

        $this->seeNumRecords(0, 'pendaftar_ppdb', []);
        $this->seeNumRecords(0, 'notifikasi', []);
    }

    public function testBelumAdaPengaturanDianggapTutup(): void
    {
        $result = $this->post('ppdb/daftar', $this->denganCsrf(self::dataValid()));

        $result->assertSessionHas('error', 'Pendaftaran sedang ditutup.');
        $this->seeNumRecords(0, 'pendaftar_ppdb', []);
    }

    public function testTanggalLahirDiMasaDepanDitolak(): void
    {
        $this->aturPendaftaran('buka');

        $result = $this->post('ppdb/daftar', $this->denganCsrf(
            ['tanggal_lahir' => date('Y-m-d', strtotime('+1 year'))] + self::dataValid()
        ));

        $result->assertRedirect();
        $result->assertSessionHas('errors', ['tanggal_lahir' => 'Tanggal lahir tidak boleh di masa depan.']);

        $this->seeNumRecords(0, 'pendaftar_ppdb', []);
        $this->seeNumRecords(0, 'notifikasi', []);
    }

    #[DataProvider('inputTidakValid')]
    public function testInputTidakValidDitolakDiFieldYangTepat(array $ubah, string $fieldError): void
    {
        $this->aturPendaftaran('buka');

        $result = $this->post('ppdb/daftar', $this->denganCsrf($ubah + self::dataValid()));

        $result->assertRedirect();
        $result->assertSessionHas('errors');

        $errors = session('errors');
        $this->assertIsArray($errors);
        $this->assertArrayNotHasKey('throttle', $errors, 'Ditolak karena rate-limit, bukan karena validasi.');
        $this->assertArrayHasKey($fieldError, $errors);

        $this->seeNumRecords(0, 'pendaftar_ppdb', []);
        $this->seeNumRecords(0, 'notifikasi', []);
    }

    public static function inputTidakValid(): array
    {
        return [
            'nama mengandung angka' => [['nama_lengkap' => 'Budi123'], 'nama_lengkap'],
            'nama terlalu pendek'   => [['nama_lengkap' => 'Bu'], 'nama_lengkap'],
            'nama kosong'           => [['nama_lengkap' => ''], 'nama_lengkap'],
            'jenis kelamin ngawur'  => [['jenis_kelamin' => 'Lainnya'], 'jenis_kelamin'],
            'no hp terlalu pendek'  => [['no_hp' => '0812'], 'no_hp'],
            'no hp ada huruf'       => [['no_hp' => '0812abc34567'], 'no_hp'],
            'email tidak valid'     => [['email' => 'bukan-email'], 'email'],
            'format tanggal salah'  => [['tanggal_lahir' => '17/05/2010'], 'tanggal_lahir'],
            'alamat kosong'         => [['alamat' => ''], 'alamat'],
            'jurusan kosong'        => [['jurusan_pilihan' => ''], 'jurusan_pilihan'],
        ];
    }

    public function testRateLimitMenolakPendaftaranKeEmpatDariIpYangSama(): void
    {
        $this->aturPendaftaran('buka');

        $nama = ['Budi Satu', 'Budi Dua', 'Budi Tiga'];

        foreach ($nama as $i => $n) {
            $result = $this->post('ppdb/daftar', $this->denganCsrf([
                'nama_lengkap' => $n,
                'no_hp'        => '08123456789' . $i,
                'email'        => 'budi' . $i . '@example.com',
            ] + self::dataValid()));

            $result->assertSessionHas('success');
        }

        $result = $this->post('ppdb/daftar', $this->denganCsrf(['nama_lengkap' => 'Budi Empat'] + self::dataValid()));

        $result->assertRedirect();
        $result->assertSessionHas('errors', [
            'throttle' => 'Terlalu banyak percobaan. Silakan coba lagi dalam beberapa saat.',
        ]);

        $this->seeNumRecords(3, 'pendaftar_ppdb', []);
        $this->dontSeeInDatabase('pendaftar_ppdb', ['nama_lengkap' => 'Budi Empat']);
    }
}
