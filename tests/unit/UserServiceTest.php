<?php

namespace Tests\Unit;

use App\Models\AuditLogModel;
use App\Models\UserModel;
use App\Services\UserService;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class UserServiceTest extends CIUnitTestCase
{
    private $model;
    private $audit;
    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = $this->createMock(UserModel::class);
        $this->audit = $this->createMock(AuditLogModel::class);

        $this->service = new UserService($this->model, $this->audit);
    }

    // ================= Daftar =================

    public function testDaftarRoleTidakDikenalMemakaiRoleAdmin(): void
    {
        $this->model->expects($this->once())
            ->method('getAllWithDetail')
            ->with(100, null, 1, 'user_ngawur')
            ->willReturn([]);

        $hasil = $this->service->daftar('ngawur');

        $this->assertSame('user_ngawur', $hasil['pagerGroup']);
        $this->assertSame('Ngawur', $hasil['roleLabel']);
    }

    public function testDaftarRoleGuruMemakaiRoleId2(): void
    {
        $this->model->expects($this->once())
            ->method('getAllWithDetail')
            ->with(100, 'budi', 2, 'user_guru')
            ->willReturn([]);

        $this->service->daftar('guru', 'budi');
    }

    // ================= Buat admin =================

    public function testBuatAdminUsernameSpasiSajaDitolak(): void
    {
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Username tidak boleh kosong/hanya spasi.');
        $this->service->buatAdmin(['username' => '    ', 'password' => 'Rahasia123!']);
    }

    public function testBuatAdminUsernameDuplikatDitolak(): void
    {
        $this->model->method('usernameSudahAda')->willReturn(true);
        $this->model->expects($this->never())->method('insert');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Username ini sudah terdaftar.');
        $this->service->buatAdmin(['username' => 'admin2', 'password' => 'Rahasia123!']);
    }

    public function testBuatAdminBerhasilRoleAdminAktifDanPasswordTerhash(): void
    {
        $this->model->method('usernameSudahAda')->willReturn(false);
        $this->model->expects($this->once())
            ->method('insert')
            ->with($this->callback(
                static fn (array $d) => $d['username'] === 'admin baru'
                    && $d['role_id'] === 1
                    && $d['status'] === 'Aktif'
                    && $d['login_attempts'] === 0
                    && $d['password'] !== 'Rahasia123!'
                    && password_verify('Rahasia123!', $d['password'])
            ))
            ->willReturn(1);

        $this->service->buatAdmin(['username' => '  admin   baru ', 'password' => 'Rahasia123!']);
    }

    public function testBuatAdminGagalDbPesanTidakKosong(): void
    {
        $this->model->method('usernameSudahAda')->willReturn(false);
        $this->model->method('insert')->willReturn(false);
        $this->model->method('errors')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Gagal membuat akun admin.');
        $this->service->buatAdmin(['username' => 'admin2', 'password' => 'Rahasia123!']);
    }

    // ================= Buka kunci =================

    public function testBukaKunciAkunSendiriDitolak(): void
    {
        $this->model->expects($this->never())->method('unlockAccount');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tidak bisa unlock akun sendiri lewat sini.');
        $this->service->bukaKunci(1, 1, 'root');
    }

    public function testBukaKunciAkunTidakAdaDitolak(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('unlockAccount');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Akun tidak ditemukan.');
        $this->service->bukaKunci(99, 1, 'root');
    }

    public function testBukaKunciBerhasilMencatatAudit(): void
    {
        $this->model->method('find')->willReturn(['id_user' => 5, 'username' => 'budi']);
        $this->model->expects($this->once())->method('unlockAccount')->with(5)->willReturn(true);
        $this->audit->expects($this->once())
            ->method('catat')
            ->with(1, 'root', 'unlock_akun', 'Membuka kunci akun budi.');

        $this->service->bukaKunci(5, 1, 'root');
    }

    public function testBukaKunciGagalDbTidakMencatatAudit(): void
    {
        $this->model->method('find')->willReturn(['id_user' => 5, 'username' => 'budi']);
        $this->model->method('unlockAccount')->willReturn(false);
        $this->audit->expects($this->never())->method('catat');

        $this->expectException(InvalidArgumentException::class);
        $this->service->bukaKunci(5, 1, 'root');
    }

    // ================= Reset password =================

    public function testResetPasswordAkunSendiriDitolak(): void
    {
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tidak bisa reset password akun sendiri lewat sini.');
        $this->service->resetPassword(1, 1, 'root');
    }

    public function testResetPasswordAkunTidakAdaDitolak(): void
    {
        $this->model->method('find')->willReturn(null);
        $this->model->expects($this->never())->method('update');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Akun tidak ditemukan.');
        $this->service->resetPassword(99, 1, 'root');
    }

    public function testResetPasswordBerhasilPasswordAcak8KarakterDanKunciDibuka(): void
    {
        $this->model->method('find')->willReturn(['id_user' => 5, 'username' => 'budi']);

        $tersimpan = null;
        $this->model->expects($this->once())
            ->method('update')
            ->with(5, $this->callback(static function (array $d) use (&$tersimpan) {
                $tersimpan = $d['password'];

                return $d['login_attempts'] === 0 && $d['locked_until'] === null;
            }))
            ->willReturn(true);

        $this->audit->expects($this->once())
            ->method('catat')
            ->with(1, 'root', 'reset_password_admin', $this->stringContains('budi'));

        $hasil = $this->service->resetPassword(5, 1, 'root');

        $this->assertSame('budi', $hasil['username']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}$/', $hasil['password']);
        // Yang disimpan ke DB adalah hash dari password yang dikembalikan, bukan teks asli
        $this->assertNotSame($hasil['password'], $tersimpan);
        $this->assertTrue(password_verify($hasil['password'], $tersimpan));
    }

    public function testResetPasswordGagalDbTidakMencatatAuditDanTidakMengembalikanPassword(): void
    {
        $this->model->method('find')->willReturn(['id_user' => 5, 'username' => 'budi']);
        $this->model->method('update')->willReturn(false);
        $this->audit->expects($this->never())->method('catat');

        $this->expectException(InvalidArgumentException::class);
        $this->service->resetPassword(5, 1, 'root');
    }
}