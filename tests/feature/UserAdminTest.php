<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\Mock\MockCache;
use Config\Database;
use Config\Services;
use PHPUnit\Framework\Attributes\DataProvider;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Feature test manajemen akun (khusus superadmin): reset password, buka kunci, buat admin,
 * terhadap database TES (web_sekolah_test). Termasuk bukti bahwa password baru yang
 * ditampilkan benar-benar berlaku, dan aksi sensitif tercatat di audit log.
 *
 * Login disimulasikan lewat sesi; 'status_checked_at' diisi supaya AuthFilter
 * tidak perlu mengecek tabel users.
 *
 * @internal
 */
final class UserAdminTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $DBGroup   = 'tests';
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = null;

    private const ID_ADMIN = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pastikanDatabaseTes();

        // Cache in-memory yang baru di tiap tes: penghitung throttle mulai dari nol.
        Services::injectMock('cache', new MockCache());
        Services::resetSingle('throttler');

        foreach (['ppdbAdminService', 'userService', 'pengumumanService'] as $nama) {
            Services::resetSingle($nama);
        }

        $this->siapkanDasar();
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
     * Kosongkan tabel yang dipakai, lalu isi data dasar: 3 role dan satu superadmin (id 1)
     * sebagai pelaku, supaya audit log dan relasi tabel punya acuan yang sah.
     */
    private function siapkanDasar(): void
    {
        $this->db->disableForeignKeyChecks();

        foreach (['audit_log', 'notifikasi', 'pendaftar_ppdb', 'pengaturan_ppdb', 'pengumuman', 'users', 'roles'] as $tabel) {
            $this->db->table($tabel)->emptyTable();
        }

        $this->db->table('roles')->insertBatch([
            ['id_role' => 1, 'nama_role' => 'Admin'],
            ['id_role' => 2, 'nama_role' => 'Guru'],
            ['id_role' => 3, 'nama_role' => 'Siswa'],
        ]);

        $this->isiUser(self::ID_ADMIN, 1, 'superadmin_tes', 1);

        $this->db->enableForeignKeyChecks();
    }

    private function isiUser(int $id, int $roleId, string $username, int $superadmin = 0, int $percobaan = 0, ?string $terkunciSampai = null): void
    {
        $this->db->table('users')->insert([
            'id_user'        => $id,
            'role_id'        => $roleId,
            'username'       => $username,
            'password'       => password_hash('PasswordLama#1', PASSWORD_BCRYPT, ['cost' => 4]),
            'status'         => 'Aktif',
            'login_attempts' => $percobaan,
            'locked_until'   => $terkunciSampai,
            'is_superadmin'  => $superadmin,
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    private function sesi(bool $superadmin = false, string $role = 'Admin'): array
    {
        return [
            'id_user'           => self::ID_ADMIN,
            'username'          => 'superadmin_tes',
            'role'              => $role,
            'role_id'           => 1,
            'logged_in'         => true,
            'is_superadmin'     => $superadmin,
            'status_checked_at' => time(),
            'last_activity'     => time(),
        ];
    }

    /**
     * Tambahkan token CSRF yang sah, seperti yang dikirim form di browser.
     */
    private function denganCsrf(array $data = []): array
    {
        helper('form');

        return [csrf_token() => csrf_hash()] + $data;
    }

    private const PASSWORD_KUAT = 'Str0ng!Passw0rd#2026';

    private function ambilUser(int $id): array
    {
        return $this->db->table('users')->where('id_user', $id)->get()->getRowArray();
    }

    // ---------- akses ----------

    public function testTanpaLoginDialihkanKeLogin(): void
    {
        $result = $this->get('admin/user');

        $result->assertRedirectTo('/login');
    }

    public function testAdminBiasaBukanSuperadminDianggapHalamanTidakAda(): void
    {
        try {
            $result = $this->withSession($this->sesi(false))->get('admin/user');
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);

            return;
        }

        $result->assertStatus(404);
    }

    public function testAdminBiasaTidakBisaResetPasswordLewatPost(): void
    {
        $this->isiUser(2, 2, 'guru_tes');
        $lama = $this->ambilUser(2)['password'];

        try {
            $this->withSession($this->sesi(false))->post('admin/user/reset-password/2', $this->denganCsrf());
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }

        $this->assertSame($lama, $this->ambilUser(2)['password'], 'Password tidak boleh berubah.');
    }

    // ---------- reset password ----------

    public function testResetPasswordMembuatPasswordBaruYangBenarBenarBerlaku(): void
    {
        $this->isiUser(2, 2, 'guru_tes', 0, 3, date('Y-m-d H:i:s', strtotime('+1 hour')));
        $lama = $this->ambilUser(2)['password'];

        $result = $this->withSession($this->sesi(true))->post('admin/user/reset-password/2', $this->denganCsrf());

        $result->assertRedirectTo('/admin/user');

        $pesan = (string) session('success');
        $this->assertStringContainsString('Password guru_tes berhasil direset', $pesan);
        $this->assertSame(1, preg_match('/Password baru: ([0-9a-f]{8})\./', $pesan, $cocok), 'Password baru harus tampil sekali di pesan.');

        $baru = $this->ambilUser(2);
        $this->assertNotSame($lama, $baru['password']);
        $this->assertTrue(password_verify($cocok[1], $baru['password']), 'Password yang ditampilkan harus benar-benar bisa dipakai login.');
        $this->assertSame(0, (int) $baru['login_attempts']);
        $this->assertNull($baru['locked_until']);

        $this->seeNumRecords(1, 'audit_log', ['aksi' => 'reset_password_admin']);
    }

    public function testResetPasswordAkunSendiriDitolak(): void
    {
        $lama = $this->ambilUser(self::ID_ADMIN)['password'];

        $result = $this->withSession($this->sesi(true))->post('admin/user/reset-password/' . self::ID_ADMIN, $this->denganCsrf());

        $result->assertRedirectTo('/admin/user');
        $result->assertSessionHas('error', 'Tidak bisa reset password akun sendiri lewat sini.');
        $this->assertSame($lama, $this->ambilUser(self::ID_ADMIN)['password']);
        $this->seeNumRecords(0, 'audit_log', ['aksi' => 'reset_password_admin']);
    }

    public function testResetPasswordAkunTidakAdaDitolak(): void
    {
        $result = $this->withSession($this->sesi(true))->post('admin/user/reset-password/999999', $this->denganCsrf());

        $result->assertRedirectTo('/admin/user');
        $result->assertSessionHas('error', 'Akun tidak ditemukan.');
        $this->seeNumRecords(0, 'audit_log', ['aksi' => 'reset_password_admin']);
    }

    // ---------- buka kunci ----------

    public function testBukaKunciMenghapusKunciDanTercatat(): void
    {
        $this->isiUser(2, 2, 'guru_tes', 0, 5, date('Y-m-d H:i:s', strtotime('+1 hour')));

        $result = $this->withSession($this->sesi(true))->post('admin/user/unlock/2', $this->denganCsrf());

        $result->assertRedirectTo('/admin/user');
        $result->assertSessionHas('success', 'Akun berhasil dibuka kembali.');

        $user = $this->ambilUser(2);
        $this->assertSame(0, (int) $user['login_attempts']);
        $this->assertNull($user['locked_until']);
        $this->seeNumRecords(1, 'audit_log', ['aksi' => 'unlock_akun']);
    }

    public function testBukaKunciAkunSendiriDitolak(): void
    {
        $result = $this->withSession($this->sesi(true))->post('admin/user/unlock/' . self::ID_ADMIN, $this->denganCsrf());

        $result->assertRedirectTo('/admin/user');
        $result->assertSessionHas('error', 'Tidak bisa unlock akun sendiri lewat sini.');
        $this->seeNumRecords(0, 'audit_log', ['aksi' => 'unlock_akun']);
    }

    // ---------- buat admin ----------

    public function testBuatAdminBaruTersimpanDenganRoleAdminDanPasswordTerenkripsi(): void
    {
        $result = $this->withSession($this->sesi(true))->post('admin/user/store-admin', $this->denganCsrf([
            'username' => 'admin_baru',
            'password' => self::PASSWORD_KUAT,
        ]));

        $result->assertRedirectTo('/admin/user');
        $result->assertSessionHas('success', 'Akun admin baru berhasil dibuat.');

        $row = $this->db->table('users')->where('username', 'admin_baru')->get()->getRowArray();
        $this->assertNotNull($row, 'Akun admin baru harus tersimpan.');
        $this->assertSame(1, (int) $row['role_id']);
        $this->assertSame('Aktif', $row['status']);
        $this->assertNotSame(self::PASSWORD_KUAT, $row['password'], 'Password tidak boleh disimpan polos.');
        $this->assertTrue(password_verify(self::PASSWORD_KUAT, $row['password']));
    }

    #[DataProvider('buatAdminTidakValid')]
    public function testBuatAdminTidakValidDitolakDiFieldYangTepat(array $input, string $fieldError): void
    {
        $result = $this->withSession($this->sesi(true))->post('admin/user/store-admin', $this->denganCsrf($input));

        $result->assertRedirect();
        $result->assertSessionHas('errors');

        $errors = session('errors');
        $this->assertIsArray($errors);
        $this->assertArrayHasKey($fieldError, $errors);

        $this->seeNumRecords(1, 'users', []); // hanya superadmin dasar
    }

    public static function buatAdminTidakValid(): array
    {
        return [
            'username terlalu pendek' => [['username' => 'abc', 'password' => self::PASSWORD_KUAT], 'username'],
            'username sudah dipakai'  => [['username' => 'superadmin_tes', 'password' => self::PASSWORD_KUAT], 'username'],
            'password lemah'          => [['username' => 'admin_baru', 'password' => '123'], 'password'],
            'password kosong'         => [['username' => 'admin_baru', 'password' => ''], 'password'],
        ];
    }

    public function testBuatContentManagerTersimpanDenganRoleContentManager(): void
    {
        $this->db->table('roles')->insert(['id_role' => 4, 'nama_role' => 'Content Manager']);

        $result = $this->withSession($this->sesi(true))->post('admin/user/store-content-manager', $this->denganCsrf([
            'username' => 'cm_baru',
            'password' => self::PASSWORD_KUAT,
        ]));

        $result->assertRedirectTo('/admin/user');

        $row = $this->db->table('users')->where('username', 'cm_baru')->get()->getRowArray();
        $this->assertNotNull($row);
        $this->assertSame(4, (int) $row['role_id']);
        $this->assertSame(0, (int) $row['is_superadmin']);
        $this->assertTrue(password_verify(self::PASSWORD_KUAT, $row['password']));
    }

    public function testBuatContentManagerTanpaRoleDitolak(): void
    {
        $result = $this->withSession($this->sesi(true))->post('admin/user/store-content-manager', $this->denganCsrf([
            'username' => 'cm_baru',
            'password' => self::PASSWORD_KUAT,
        ]));

        $result->assertRedirect();
        $this->seeNumRecords(0, 'users', ['username' => 'cm_baru']);
    }

    public function testAdminBiasaTidakBisaMembuatContentManager(): void
    {
        $this->db->table('roles')->insert(['id_role' => 4, 'nama_role' => 'Content Manager']);

        try {
            $this->withSession($this->sesi(false))->post('admin/user/store-content-manager', $this->denganCsrf([
                'username' => 'cm_baru',
                'password' => self::PASSWORD_KUAT,
            ]));
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }

        $this->seeNumRecords(0, 'users', ['username' => 'cm_baru']);
    }
}
