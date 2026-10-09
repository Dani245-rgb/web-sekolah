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
 * Feature test PPDB sisi admin: ubah status, hapus, dan pengaturan pendaftaran,
 * lengkap dengan filter login/role, terhadap database TES (web_sekolah_test).
 *
 * Login admin disimulasikan lewat sesi; 'status_checked_at' diisi supaya AuthFilter
 * tidak perlu mengecek tabel users.
 *
 * @internal
 */
final class PpdbAdminTest extends CIUnitTestCase
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

    private function isiPendaftar(string $nama = 'Budi Santoso', string $noHp = '081234567890', string $status = 'Menunggu'): int
    {
        $this->db->table('pendaftar_ppdb')->insert([
            'nama_lengkap'    => $nama,
            'tempat_lahir'    => 'Bandung',
            'tanggal_lahir'   => '2010-05-17',
            'jenis_kelamin'   => 'Laki-laki',
            'asal_sekolah'    => 'SMP Negeri 1',
            'jurusan_pilihan' => 'TKJ',
            'no_hp'           => $noHp,
            'alamat'          => 'Jl. Merdeka No. 1',
            'status'          => $status,
            'tahun_ajaran'    => '2026/2027',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    // ---------- akses ----------

    public function testTanpaLoginDialihkanKeLoginDanStatusTidakBerubah(): void
    {
        $id = $this->isiPendaftar();

        $result = $this->post('admin/ppdb/update-status/' . $id, $this->denganCsrf(['status' => 'Diterima']));

        $result->assertRedirectTo('/login');
        $this->seeInDatabase('pendaftar_ppdb', ['id' => $id, 'status' => 'Menunggu']);
    }

    public function testRoleBukanAdminDitolak(): void
    {
        $id = $this->isiPendaftar();

        $result = $this->withSession($this->sesi(false, 'Siswa'))
            ->post('admin/ppdb/update-status/' . $id, $this->denganCsrf(['status' => 'Diterima']));

        $result->assertRedirectTo('/login');
        $this->seeInDatabase('pendaftar_ppdb', ['id' => $id, 'status' => 'Menunggu']);
    }

    // ---------- ubah status ----------

    #[DataProvider('statusValid')]
    public function testUbahStatusValidTersimpan(string $status): void
    {
        $id = $this->isiPendaftar();

        $result = $this->withSession($this->sesi())
            ->post('admin/ppdb/update-status/' . $id, $this->denganCsrf(['status' => $status]));

        $result->assertRedirectTo('/admin/ppdb/detail/' . $id);
        $result->assertSessionHas('success', 'Status pendaftar berhasil diperbarui.');
        $this->seeInDatabase('pendaftar_ppdb', ['id' => $id, 'status' => $status]);
    }

    public static function statusValid(): array
    {
        return [
            'diterima' => ['Diterima'],
            'ditolak'  => ['Ditolak'],
        ];
    }

    public function testUbahStatusNgawurDitolakDanTidakBerubah(): void
    {
        $id = $this->isiPendaftar();

        $result = $this->withSession($this->sesi())
            ->post('admin/ppdb/update-status/' . $id, $this->denganCsrf(['status' => 'Lulus Banget']));

        $result->assertRedirect();
        $result->assertSessionHas('error', 'Status tidak valid.');
        $this->seeInDatabase('pendaftar_ppdb', ['id' => $id, 'status' => 'Menunggu']);
    }

    public function testUbahStatusPendaftarTidakAdaDitolak(): void
    {
        $result = $this->withSession($this->sesi())
            ->post('admin/ppdb/update-status/999999', $this->denganCsrf(['status' => 'Diterima']));

        $result->assertRedirect();
        $result->assertSessionHas('error', 'Data tidak ditemukan.');
    }

    // ---------- hapus ----------

    public function testHapusPendaftarMenghapusBarisYangDipilihSaja(): void
    {
        $hapus  = $this->isiPendaftar('Budi Santoso', '081234567890');
        $tinggal = $this->isiPendaftar('Siti Aminah', '081234567891');

        $result = $this->withSession($this->sesi())->post('admin/ppdb/delete/' . $hapus, $this->denganCsrf());

        $result->assertRedirectTo('/admin/ppdb');
        $result->assertSessionHas('success', 'Data pendaftar berhasil dihapus.');
        $this->dontSeeInDatabase('pendaftar_ppdb', ['id' => $hapus]);
        $this->seeInDatabase('pendaftar_ppdb', ['id' => $tinggal]);
    }

    public function testHapusPendaftarTidakAdaDitolak(): void
    {
        $id = $this->isiPendaftar();

        $result = $this->withSession($this->sesi())->post('admin/ppdb/delete/999999', $this->denganCsrf());

        $result->assertRedirectTo('/admin/ppdb');
        $result->assertSessionHas('error', 'Data tidak ditemukan.');
        $this->seeInDatabase('pendaftar_ppdb', ['id' => $id]);
    }

    // ---------- pengaturan ----------

    public function testBukaPendaftaranTersimpan(): void
    {
        $result = $this->withSession($this->sesi())->post('admin/ppdb/pengaturan/update', $this->denganCsrf([
            'status'       => 'buka',
            'pesan_tutup'  => 'Belum dibuka.',
            'tahun_ajaran' => '2026/2027',
        ]));

        $result->assertRedirectTo('/admin/ppdb/pengaturan');
        $result->assertSessionHas('success', 'Pengaturan PPDB berhasil diperbarui.');
        $this->seeInDatabase('pengaturan_ppdb', ['id' => 1, 'status' => 'buka', 'tahun_ajaran' => '2026/2027']);
    }

    public function testPengaturanDisimpanLaluBisaDiubahLagi(): void
    {
        $this->withSession($this->sesi())->post('admin/ppdb/pengaturan/update', $this->denganCsrf([
            'status' => 'buka', 'pesan_tutup' => '', 'tahun_ajaran' => '2026/2027',
        ]));

        $result = $this->withSession($this->sesi())->post('admin/ppdb/pengaturan/update', $this->denganCsrf([
            'status' => 'tutup', 'pesan_tutup' => 'Sudah penuh.', 'tahun_ajaran' => '2026/2027',
        ]));

        $result->assertRedirectTo('/admin/ppdb/pengaturan');
        $this->seeNumRecords(1, 'pengaturan_ppdb', []);
        $this->seeInDatabase('pengaturan_ppdb', ['id' => 1, 'status' => 'tutup', 'pesan_tutup' => 'Sudah penuh.']);
    }

    #[DataProvider('pengaturanTidakValid')]
    public function testPengaturanTidakValidDitolakDanTidakTersimpan(array $input, string $pesan): void
    {
        $result = $this->withSession($this->sesi())->post('admin/ppdb/pengaturan/update', $this->denganCsrf($input));

        $result->assertRedirect();
        $result->assertSessionHas('error', $pesan);
        $this->seeNumRecords(0, 'pengaturan_ppdb', []);
    }

    public static function pengaturanTidakValid(): array
    {
        return [
            'buka tanpa tahun ajaran' => [
                ['status' => 'buka', 'pesan_tutup' => '', 'tahun_ajaran' => ''],
                'Isi tahun ajaran dulu sebelum membuka pendaftaran.',
            ],
            'status kosong' => [
                ['status' => '', 'pesan_tutup' => '', 'tahun_ajaran' => '2026/2027'],
                'Status pendaftaran wajib diisi.',
            ],
            'status ngawur' => [
                ['status' => 'setengah', 'pesan_tutup' => '', 'tahun_ajaran' => '2026/2027'],
                'Status pendaftaran tidak valid.',
            ],
            'pesan tutup kepanjangan' => [
                ['status' => 'tutup', 'pesan_tutup' => str_repeat('a', 256), 'tahun_ajaran' => '2026/2027'],
                'Pesan tutup maksimal 255 karakter.',
            ],
            'tahun ajaran kepanjangan' => [
                ['status' => 'tutup', 'pesan_tutup' => '', 'tahun_ajaran' => str_repeat('1', 21)],
                'Tahun ajaran maksimal 20 karakter.',
            ],
        ];
    }

    public function testPengaturanDariAdminMembukaFormPublik(): void
    {
        $this->withSession($this->sesi())->post('admin/ppdb/pengaturan/update', $this->denganCsrf([
            'status' => 'buka', 'pesan_tutup' => '', 'tahun_ajaran' => '2026/2027',
        ]));

        // Setelah admin membuka pendaftaran, pendaftar publik (tanpa login) bisa mendaftar
        // dan datanya memakai tahun ajaran yang diatur admin.
        Services::resetSingle('ppdbService');

        $result = $this->post('ppdb/daftar', $this->denganCsrf([
            'nama_lengkap'    => 'Budi Santoso',
            'tempat_lahir'    => 'Bandung',
            'tanggal_lahir'   => '2010-05-17',
            'jenis_kelamin'   => 'Laki-laki',
            'asal_sekolah'    => 'SMP Negeri 1',
            'jurusan_pilihan' => 'TKJ',
            'no_hp'           => '081234567890',
            'email'           => '',
            'alamat'          => 'Jl. Merdeka No. 1',
        ]));

        $result->assertRedirectTo('/ppdb');
        $result->assertSessionHas('success');
        $this->seeInDatabase('pendaftar_ppdb', ['nama_lengkap' => 'Budi Santoso', 'tahun_ajaran' => '2026/2027']);
    }
}
