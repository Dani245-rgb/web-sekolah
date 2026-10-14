<?php

namespace Tests\Feature;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\Mock\MockCache;
use Config\Database;
use Config\Services;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Mengunci aturan akses halaman khusus superadmin SEBELUM cek is_superadmin
 * dipindah ke filter. Hanya membuka halaman (GET), tidak mengubah data.
 *
 * Perilaku yang dikunci:
 * - belum login            -> dialihkan ke /login
 * - role selain Admin      -> dialihkan ke /login (RoleFilter)
 * - Admin biasa            -> 404
 * - Admin + is_superadmin  -> 200
 *
 * @internal
 */
final class AksesPerRoleTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $DBGroup   = 'tests';
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = null;

    private const ID_USER = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pastikanDatabaseTes();

        Services::injectMock('cache', new MockCache());
        Services::resetSingle('throttler');

        $this->siapkanDasar();
    }

    private function pastikanDatabaseTes(): void
    {
        $this->assertSame('testing', ENVIRONMENT, 'ENVIRONMENT harus "testing" saat PHPUnit jalan.');
        $this->assertSame('tests', config('Database')->defaultGroup, 'Grup default harus "tests".');
        $this->assertSame('web_sekolah_test', Database::connect()->getDatabase());
        $this->assertSame('web_sekolah_test', $this->db->getDatabase());
    }

    private function siapkanDasar(): void
    {
        $this->db->disableForeignKeyChecks();

        foreach (['audit_log', 'notifikasi', 'users', 'roles'] as $tabel) {
            $this->db->table($tabel)->emptyTable();
        }

        $this->db->table('roles')->insertBatch([
            ['id_role' => 1, 'nama_role' => 'Admin'],
            ['id_role' => 4, 'nama_role' => 'Content Manager'],
        ]);

        $this->db->table('users')->insert([
            'id_user'        => self::ID_USER,
            'role_id'        => 1,
            'username'       => 'pengguna_tes',
            'password'       => password_hash('PasswordLama#1', PASSWORD_BCRYPT, ['cost' => 4]),
            'status'         => 'Aktif',
            'login_attempts' => 0,
            'is_superadmin'  => 1,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->db->enableForeignKeyChecks();
    }

    private function sesi(bool $superadmin, string $role = 'Admin'): array
    {
        return [
            'id_user'           => self::ID_USER,
            'username'          => 'pengguna_tes',
            'role'              => $role,
            'role_id'           => 1,
            'logged_in'         => true,
            'is_superadmin'     => $superadmin,
            'status_checked_at' => time(),
            'last_activity'     => time(),
        ];
    }

    /**
     * Return hasil request, atau null kalau aplikasi melempar PageNotFoundException (= 404).
     */
    private function buka(string $url, array $sesi)
    {
        try {
            return $this->withSession($sesi)->get($url);
        } catch (PageNotFoundException) {
            return null;
        }
    }

    public static function halamanKhususSuperadmin(): array
    {
        return [
            'audit log'       => ['admin/audit-log'],
            'backup database' => ['admin/backup-database'],
            'role permission' => ['admin/role-permission'],
            'daftar user'     => ['admin/user'],
        ];
    }

    #[DataProvider('halamanKhususSuperadmin')]
    public function testTanpaLoginDialihkanKeLogin(string $url): void
    {
        $this->get($url)->assertRedirectTo('/login');
    }

    #[DataProvider('halamanKhususSuperadmin')]
    public function testRoleSelainAdminDialihkanKeLogin(string $url): void
    {
        $result = $this->buka($url, $this->sesi(false, 'Tamu'));

        $this->assertNotNull($result, 'Role Tamu harus ditolak oleh RoleFilter, bukan sampai ke controller.');
        $result->assertRedirectTo('/login');
    }

    #[DataProvider('halamanKhususSuperadmin')]
    public function testAdminBiasaDianggapHalamanTidakAda(string $url): void
    {
        $result = $this->buka($url, $this->sesi(false));

        if ($result === null) {
            $this->addToAssertionCount(1);

            return;
        }

        $result->assertStatus(404);
    }

    #[DataProvider('halamanKhususSuperadmin')]
    public function testSuperadminBisaMembuka(string $url): void
    {
        $result = $this->buka($url, $this->sesi(true));

        $this->assertNotNull($result, 'Superadmin tidak boleh mendapat 404.');
        $result->assertStatus(200);
    }

    public static function halamanKhususAdmin(): array
    {
        return [
            'guru'          => ['admin/guru'],
            'siswa'         => ['admin/siswa'],
            'import siswa'  => ['admin/siswa/import'],
            'ppdb'          => ['admin/ppdb'],
            'pesan masuk'   => ['admin/kontak'],
            'pengaturan'    => ['admin/pengaturan'],
            'laporan siswa' => ['admin/laporan-siswa'],
            'notifikasi'    => ['admin/notifikasi'],
            'pkl'           => ['admin/pkl'],
        ];
    }

    #[DataProvider('halamanKhususAdmin')]
    public function testContentManagerDitolakDiHalamanKhususAdmin(string $url): void
    {
        $result = $this->buka($url, $this->sesi(false, 'Content Manager'));

        $this->assertNotNull($result, 'Harus ditolak filter, bukan sampai ke controller.');
        $result->assertRedirectTo('/login');
    }

    #[DataProvider('halamanKhususSuperadmin')]
    public function testContentManagerDitolakDiHalamanSuperadmin(string $url): void
    {
        $result = $this->buka($url, $this->sesi(false, 'Content Manager'));

        $this->assertNotNull($result);
        $result->assertRedirectTo('/login');
    }

    public static function halamanCms(): array
    {
        return [
            'berita'     => ['admin/berita'],
            'galeri'     => ['admin/galeri'],
            'pengumuman' => ['admin/pengumuman'],
            'prestasi'   => ['admin/prestasi'],
            'jurusan'    => ['admin/jurusan'],
        ];
    }

    #[DataProvider('halamanCms')]
    public function testContentManagerBisaMembukaHalamanCms(string $url): void
    {
        $result = $this->buka($url, $this->sesi(false, 'Content Manager'));

        $this->assertNotNull($result, 'Content Manager tidak boleh mendapat 404.');
        $result->assertStatus(200);
    }
}
