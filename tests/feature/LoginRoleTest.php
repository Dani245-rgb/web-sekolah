<?php

namespace Tests\Feature;

use App\Models\AuditLogModel;
use App\Models\NotifikasiModel;
use App\Models\UserModel;
use App\Services\AuthService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * Membuktikan role mana yang boleh login, berdasarkan Config\HakAkses::$portalLogin.
 * Memakai database TES, dan memanggil AuthService langsung (tanpa throttle/CSRF/session).
 *
 * @internal
 */
final class LoginRoleTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $DBGroup   = 'tests';
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = null;

    private const PASSWORD = 'PasswordTes#1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('testing', ENVIRONMENT, 'ENVIRONMENT harus "testing".');
        $this->assertSame('web_sekolah_test', Database::connect()->getDatabase());
        $this->assertSame('web_sekolah_test', $this->db->getDatabase());

        $this->db->disableForeignKeyChecks();

        foreach (['audit_log', 'notifikasi', 'users', 'roles'] as $tabel) {
            $this->db->table($tabel)->emptyTable();
        }

        $this->db->table('roles')->insertBatch([
            ['id_role' => 1, 'nama_role' => 'Admin'],
            ['id_role' => 2, 'nama_role' => 'Content Manager'],
            ['id_role' => 3, 'nama_role' => 'Tamu'],
        ]);

        $this->isiUser(1, 1, 'admin_tes');
        $this->isiUser(2, 2, 'cm_tes');
        $this->isiUser(3, 3, 'tamu_tes');

        $this->db->enableForeignKeyChecks();
    }

    private function isiUser(int $id, int $roleId, string $username): void
    {
        $this->db->table('users')->insert([
            'id_user'        => $id,
            'role_id'        => $roleId,
            'username'       => $username,
            'password'       => password_hash(self::PASSWORD, PASSWORD_BCRYPT, ['cost' => 4]),
            'status'         => 'Aktif',
            'login_attempts' => 0,
            'is_superadmin'  => 0,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    private function service(): AuthService
    {
        return new AuthService(new UserModel(), new AuditLogModel(), new NotifikasiModel());
    }

    public function testAdminBerhasilLogin(): void
    {
        $hasil = $this->service()->login('admin_tes', self::PASSWORD);

        $this->assertSame(AuthService::SUKSES, $hasil['status']);
        $this->assertSame('Admin', $hasil['user']['nama_role']);
    }

    public function testContentManagerBerhasilLogin(): void
    {
        $hasil = $this->service()->login('cm_tes', self::PASSWORD);

        $this->assertSame(AuthService::SUKSES, $hasil['status']);
        $this->assertSame('Content Manager', $hasil['user']['nama_role']);
    }

    public function testRoleLainDitolak(): void
    {
        $hasil = $this->service()->login('tamu_tes', self::PASSWORD);

        $this->assertSame(AuthService::ROLE_DITOLAK, $hasil['status']);
        $this->assertNull($hasil['user']);
        $this->seeNumRecords(1, 'audit_log', ['aksi' => 'login_ditolak_role_belum_tersedia']);
    }

    public function testPasswordSalahContentManagerGagal(): void
    {
        $hasil = $this->service()->login('cm_tes', 'salah');

        $this->assertSame(AuthService::GAGAL, $hasil['status']);
    }

    public function testTujuanSetelahLoginDibacaDariKonfigurasi(): void
    {
        $portal = config('HakAkses')->portalLogin;

        $this->assertSame('/admin/dashboard', $portal['Admin']);
        $this->assertSame('/admin/dashboard', $portal['Content Manager']);
        $this->assertArrayNotHasKey('Tamu', $portal);
    }
}