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
 * Feature test CRUD pengumuman sisi admin terhadap database TES (web_sekolah_test):
 * tambah, ubah, hapus, validasi, dan judul unik, lengkap dengan filter login/role.
 *
 * Login admin disimulasikan lewat sesi; 'status_checked_at' diisi supaya AuthFilter
 * tidak perlu mengecek tabel users.
 *
 * @internal
 */
final class PengumumanAdminTest extends CIUnitTestCase
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

    private static function dataValid(): array
    {
        return [
            'judul'           => 'Libur Semester Ganjil',
            'isi'             => 'Libur dimulai 20 Desember.',
            'tanggal_publish' => '2026-10-10',
            'status'          => 'Published',
        ];
    }

    private function isiPengumuman(string $judul = 'Pengumuman Lama', string $slug = 'pengumuman-lama'): int
    {
        $this->db->table('pengumuman')->insert([
            'judul'           => $judul,
            'slug'            => $slug,
            'isi'             => 'Isi lama.',
            'tanggal_publish' => '2026-10-01',
            'status'          => 'Draft',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    // ---------- akses ----------

    public function testTanpaLoginDialihkanKeLoginDanTidakTersimpan(): void
    {
        $result = $this->post('admin/pengumuman/store', $this->denganCsrf(self::dataValid()));

        $result->assertRedirectTo('/login');
        $this->seeNumRecords(0, 'pengumuman', []);
    }

    public function testRoleBukanAdminDitolak(): void
    {
        $result = $this->withSession($this->sesi(false, 'Siswa'))
            ->post('admin/pengumuman/store', $this->denganCsrf(self::dataValid()));

        $result->assertRedirectTo('/login');
        $this->seeNumRecords(0, 'pengumuman', []);
    }

    // ---------- tambah ----------

    public function testTambahPengumumanValidTersimpanDenganSlug(): void
    {
        $result = $this->withSession($this->sesi())->post('admin/pengumuman/store', $this->denganCsrf(self::dataValid()));

        $result->assertRedirectTo('/admin/pengumuman');
        $result->assertSessionHas('success', 'Pengumuman berhasil ditambahkan.');

        $row = $this->db->table('pengumuman')->where('judul', 'Libur Semester Ganjil')->get()->getRowArray();
        $this->assertNotNull($row);
        $this->assertSame('Published', $row['status']);
        $this->assertSame('2026-10-10', $row['tanggal_publish']);
        $this->assertNotSame('', trim((string) $row['slug']), 'Slug harus dibuat otomatis.');
    }

    public function testTambahJudulSudahDipakaiDitolak(): void
    {
        $this->isiPengumuman('Libur Semester Ganjil', 'libur-semester-ganjil');

        $result = $this->withSession($this->sesi())->post('admin/pengumuman/store', $this->denganCsrf(self::dataValid()));

        $result->assertRedirect();
        $result->assertSessionHas('errors', ['pengumuman' => 'Judul pengumuman sudah dipakai.']);
        $this->seeNumRecords(1, 'pengumuman', []);
    }

    #[DataProvider('inputTidakValid')]
    public function testTambahInputTidakValidDitolakDenganPesanYangTepat(array $ubah, string $pesan): void
    {
        $result = $this->withSession($this->sesi())->post('admin/pengumuman/store', $this->denganCsrf($ubah + self::dataValid()));

        $result->assertRedirect();
        $result->assertSessionHas('errors');

        $errors = session('errors');
        $this->assertIsArray($errors);
        $this->assertArrayHasKey('pengumuman', $errors);
        $this->assertStringContainsString($pesan, (string) $errors['pengumuman']);

        $this->seeNumRecords(0, 'pengumuman', []);
    }

    public static function inputTidakValid(): array
    {
        return [
            'judul kosong'        => [['judul' => ''], 'Judul wajib diisi.'],
            'judul terlalu pendek' => [['judul' => 'ab'], 'Judul minimal 3 karakter.'],
            'isi kosong'          => [['isi' => '   '], 'Isi pengumuman wajib diisi.'],
            'tanggal kosong'      => [['tanggal_publish' => ''], 'Tanggal publish wajib diisi.'],
            'tanggal ngawur'      => [['tanggal_publish' => 'bukan-tanggal'], 'Tanggal publish tidak valid.'],
            'status ngawur'       => [['status' => 'Rahasia'], 'Status harus Published atau Draft.'],
        ];
    }

    // ---------- ubah ----------

    public function testUbahPengumumanTersimpan(): void
    {
        $id = $this->isiPengumuman();

        $result = $this->withSession($this->sesi())->post('admin/pengumuman/update/' . $id, $this->denganCsrf(self::dataValid()));

        $result->assertRedirectTo('/admin/pengumuman');
        $result->assertSessionHas('success', 'Pengumuman berhasil diperbarui.');
        $this->seeInDatabase('pengumuman', ['id' => $id, 'judul' => 'Libur Semester Ganjil', 'status' => 'Published']);
        $this->seeNumRecords(1, 'pengumuman', []);
    }

    public function testUbahDenganJudulSamaMilikSendiriBoleh(): void
    {
        $id = $this->isiPengumuman('Libur Semester Ganjil', 'libur-semester-ganjil');

        $result = $this->withSession($this->sesi())->post('admin/pengumuman/update/' . $id, $this->denganCsrf(
            ['isi' => 'Isi sudah diperbarui.'] + self::dataValid()
        ));

        $result->assertRedirectTo('/admin/pengumuman');
        $this->seeInDatabase('pengumuman', ['id' => $id, 'isi' => 'Isi sudah diperbarui.']);
    }

    public function testUbahDenganJudulMilikPengumumanLainDitolak(): void
    {
        $this->isiPengumuman('Libur Semester Ganjil', 'libur-semester-ganjil');
        $id = $this->isiPengumuman('Pengumuman Lain', 'pengumuman-lain');

        $result = $this->withSession($this->sesi())->post('admin/pengumuman/update/' . $id, $this->denganCsrf(self::dataValid()));

        $result->assertRedirect();
        $result->assertSessionHas('errors', ['pengumuman' => 'Judul pengumuman sudah dipakai.']);
        $this->seeInDatabase('pengumuman', ['id' => $id, 'judul' => 'Pengumuman Lain']);
    }

    public function testUbahPengumumanTidakAdaDitolak(): void
    {
        $result = $this->withSession($this->sesi())->post('admin/pengumuman/update/999999', $this->denganCsrf(self::dataValid()));

        $result->assertRedirectTo('/admin/pengumuman');
        $result->assertSessionHas('error', 'Data tidak ditemukan.');
    }

    // ---------- hapus ----------

    public function testHapusPengumumanMenghapusBarisYangDipilihSaja(): void
    {
        $hapus   = $this->isiPengumuman('Akan Dihapus', 'akan-dihapus');
        $tinggal = $this->isiPengumuman('Tetap Ada', 'tetap-ada');

        $result = $this->withSession($this->sesi())->post('admin/pengumuman/delete/' . $hapus, $this->denganCsrf());

        $result->assertRedirectTo('/admin/pengumuman');
        $result->assertSessionHas('success', 'Pengumuman berhasil dihapus.');
        $this->dontSeeInDatabase('pengumuman', ['id' => $hapus]);
        $this->seeInDatabase('pengumuman', ['id' => $tinggal]);
    }

    public function testHapusPengumumanTidakAdaDitolak(): void
    {
        $id = $this->isiPengumuman();

        $result = $this->withSession($this->sesi())->post('admin/pengumuman/delete/999999', $this->denganCsrf());

        $result->assertRedirectTo('/admin/pengumuman');
        $result->assertSessionHas('error', 'Data tidak ditemukan.');
        $this->seeInDatabase('pengumuman', ['id' => $id]);
    }
}
