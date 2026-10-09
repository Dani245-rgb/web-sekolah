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
 * Feature test sisi admin untuk notifikasi: endpoint JSON poll dan aksi baca,
 * lengkap dengan filter login/role, terhadap database TES (web_sekolah_test).
 *
 * Login admin disimulasikan lewat sesi (sama seperti hasil Login::authenticate),
 * 'status_checked_at' diisi supaya AuthFilter tidak perlu mengecek tabel users.
 *
 * @internal
 */
final class NotifikasiAdminTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $DBGroup   = 'tests';
    protected $migrate   = false;
    protected $refresh   = false;
    protected $namespace = null;

    private const ID_ADMIN = 1;
    private const ID_LAIN  = 2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pastikanDatabaseTes();

        // Cache in-memory yang baru di tiap tes: penghitung throttle mulai dari nol.
        Services::injectMock('cache', new MockCache());
        Services::resetSingle('throttler');

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

    private function sesiAdmin(string $role = 'Admin'): array
    {
        return [
            'id_user'           => self::ID_ADMIN,
            'username'          => 'admin_tes',
            'role'              => $role,
            'role_id'           => 1,
            'logged_in'         => true,
            'is_superadmin'     => false,
            'status_checked_at' => time(),
            'last_activity'     => time(),
        ];
    }

    /**
     * Isi satu notifikasi, return id-nya. Foreign key dimatikan sebentar supaya
     * kita tidak perlu mengisi tabel users hanya untuk menguji notifikasi.
     */
    private function isiNotif(?int $userId, string $judul, int $dibaca = 0, ?string $link = null, string $waktu = '2026-10-09 10:00:00'): int
    {
        $this->db->disableForeignKeyChecks();
        $this->db->table('notifikasi')->insert([
            'user_id'    => $userId,
            'judul'      => $judul,
            'pesan'      => null,
            'jenis'      => 'tes',
            'link'       => $link,
            'is_read'    => $dibaca,
            'created_at' => $waktu,
        ]);
        $id = (int) $this->db->insertID();
        $this->db->enableForeignKeyChecks();

        return $id;
    }

    /**
     * Tambahkan token CSRF yang sah, seperti yang dikirim form di browser.
     */
    private function denganCsrf(array $data = []): array
    {
        helper('form');

        return [csrf_token() => csrf_hash()] + $data;
    }

    // ---------- poll: akses ----------

    public function testPollTanpaLoginDialihkanKeLogin(): void
    {
        $result = $this->get('admin/notifikasi/poll');

        $result->assertRedirectTo('/login');
    }

    public function testPollRoleBukanAdminDitolak(): void
    {
        $result = $this->withSession($this->sesiAdmin('Siswa'))->get('admin/notifikasi/poll');

        $result->assertRedirectTo('/login');
    }

    // ---------- poll: isi JSON ----------

    public function testPollAdminMengembalikanJsonHanyaMilikSendiriDanBroadcast(): void
    {
        $this->isiNotif(null, 'A broadcast belum dibaca', 0, null, '2026-10-09 10:03:00');
        $this->isiNotif(null, 'B broadcast sudah dibaca', 1, null, '2026-10-09 10:02:00');
        $this->isiNotif(self::ID_ADMIN, 'C milik admin belum dibaca', 0, null, '2026-10-09 10:01:00');
        $this->isiNotif(self::ID_LAIN, 'D milik user lain', 0, null, '2026-10-09 10:04:00');

        $result = $this->withSession($this->sesiAdmin())->get('admin/notifikasi/poll');

        $result->assertOK();

        $json = json_decode((string) $result->getJSON(), true);
        $this->assertIsArray($json);
        $this->assertSame(2, (int) $json['unread_count'], 'Belum dibaca = A + C (D milik user lain tidak dihitung).');

        $judul = array_column($json['terbaru'], 'judul');
        $this->assertCount(3, $judul);
        $this->assertNotContains('D milik user lain', $judul);
        $this->assertSame('A broadcast belum dibaca', $judul[0], 'Urutan: terbaru dulu.');
    }

    public function testPollTanpaNotifikasiMengembalikanNol(): void
    {
        $result = $this->withSession($this->sesiAdmin())->get('admin/notifikasi/poll');

        $result->assertOK();

        $json = json_decode((string) $result->getJSON(), true);
        $this->assertSame(0, (int) $json['unread_count']);
        $this->assertSame([], $json['terbaru']);
    }

    // ---------- baca ----------

    public function testBacaLinkInternalMengarahKeLinkDanMenandaiDibaca(): void
    {
        $id = $this->isiNotif(null, 'Pesan baru', 0, '/admin/kontak/detail/5');

        $result = $this->withSession($this->sesiAdmin())->post('admin/notifikasi/baca/' . $id, $this->denganCsrf());

        $result->assertRedirectTo('/admin/kontak/detail/5');
        $this->seeInDatabase('notifikasi', ['id_notif' => $id, 'is_read' => 1]);
    }

    #[DataProvider('linkLuar')]
    public function testBacaLinkLuarDitolakTapiTetapDitandaiDibaca(string $link): void
    {
        $id = $this->isiNotif(null, 'Notif berbahaya', 0, $link);

        $result = $this->withSession($this->sesiAdmin())->post('admin/notifikasi/baca/' . $id, $this->denganCsrf());

        $result->assertRedirectTo('/admin/notifikasi');
        $this->seeInDatabase('notifikasi', ['id_notif' => $id, 'is_read' => 1]);
    }

    public static function linkLuar(): array
    {
        return [
            'alamat situs lain'      => ['https://situs-lain.example.com/phishing'],
            'protocol-relative'      => ['//situs-lain.example.com'],
            'javascript'             => ['javascript:alert(1)'],
        ];
    }

    public function testBacaNotifikasiMilikUserLainDitolakDanTidakBerubah(): void
    {
        $id = $this->isiNotif(self::ID_LAIN, 'Rahasia user lain', 0, '/admin/kontak/detail/9');

        $result = $this->withSession($this->sesiAdmin())->post('admin/notifikasi/baca/' . $id, $this->denganCsrf());

        $result->assertRedirectTo('/admin/notifikasi');
        $result->assertSessionHas('error', 'Notifikasi tidak ditemukan.');
        $this->seeInDatabase('notifikasi', ['id_notif' => $id, 'is_read' => 0]);
    }

    public function testBacaNotifikasiTidakAdaDitolak(): void
    {
        $result = $this->withSession($this->sesiAdmin())->post('admin/notifikasi/baca/999999', $this->denganCsrf());

        $result->assertRedirectTo('/admin/notifikasi');
        $result->assertSessionHas('error', 'Notifikasi tidak ditemukan.');
    }

    public function testBacaTanpaLoginDialihkanKeLoginDanTidakBerubah(): void
    {
        $id = $this->isiNotif(null, 'Pesan baru', 0, '/admin/kontak/detail/5');

        $result = $this->post('admin/notifikasi/baca/' . $id, $this->denganCsrf());

        $result->assertRedirectTo('/login');
        $this->seeInDatabase('notifikasi', ['id_notif' => $id, 'is_read' => 0]);
    }

    // ---------- baca semua ----------

    public function testBacaSemuaHanyaMenandaiMilikSendiriDanBroadcast(): void
    {
        $a = $this->isiNotif(null, 'A broadcast');
        $c = $this->isiNotif(self::ID_ADMIN, 'C milik admin');
        $d = $this->isiNotif(self::ID_LAIN, 'D milik user lain');

        $result = $this->withSession($this->sesiAdmin())->post('admin/notifikasi/baca-semua', $this->denganCsrf());

        $result->assertRedirect();
        $this->seeInDatabase('notifikasi', ['id_notif' => $a, 'is_read' => 1]);
        $this->seeInDatabase('notifikasi', ['id_notif' => $c, 'is_read' => 1]);
        $this->seeInDatabase('notifikasi', ['id_notif' => $d, 'is_read' => 0]);
    }
}
