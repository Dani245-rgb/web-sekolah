<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function siswaService($getShared = true): \App\Services\SiswaService
    {
        if ($getShared) {
            return static::getSharedInstance('siswaService');
        }

        return new \App\Services\SiswaService(
            model(\App\Models\SiswaModel::class),
            model(\App\Models\JurusanModel::class),
            new \App\Libraries\ImageCompressor(),
        );
    }

    public static function fileUploadService($getShared = true): \App\Services\FileUploadService
    {
        if ($getShared) {
            return static::getSharedInstance('fileUploadService');
        }

        return new \App\Services\FileUploadService();
    }

    public static function slugService($getShared = true): \App\Services\SlugService
    {
        if ($getShared) {
            return static::getSharedInstance('slugService');
        }

        return new \App\Services\SlugService();
    }

    public static function authService($getShared = true): \App\Services\AuthService
    {
        if ($getShared) {
            return static::getSharedInstance('authService');
        }

        return new \App\Services\AuthService(
            model(\App\Models\UserModel::class),
            model(\App\Models\AuditLogModel::class),
            model(\App\Models\NotifikasiModel::class),
        );
    }

    public static function backupService($getShared = true): \App\Services\BackupService
    {
        if ($getShared) {
            return static::getSharedInstance('backupService');
        }

        return new \App\Services\BackupService();
    }

    public static function laporanSiswaService($getShared = true): \App\Services\LaporanSiswaService
    {
        if ($getShared) {
            return static::getSharedInstance('laporanSiswaService');
        }

        return new \App\Services\LaporanSiswaService(
            model(\App\Models\SiswaModel::class),
        );
    }

    public static function dashboardService($getShared = true): \App\Services\DashboardService
    {
        if ($getShared) {
            return static::getSharedInstance('dashboardService');
        }

        return new \App\Services\DashboardService(
            model(\App\Models\SiswaModel::class),
            model(\App\Models\GuruModel::class),
            model(\App\Models\PengumumanModel::class),
            model(\App\Models\NotifikasiModel::class),
            model(\App\Models\AuditLogModel::class),
        );
    }

    public static function berandaService($getShared = true): \App\Services\BerandaService
    {
        if ($getShared) {
            return static::getSharedInstance('berandaService');
        }

        return new \App\Services\BerandaService(
            model(\App\Models\BeritaModel::class),
            model(\App\Models\GaleriModel::class),
            model(\App\Models\PengumumanModel::class),
            model(\App\Models\AgendaModel::class),
            model(\App\Models\PrestasiModel::class),
            model(\App\Models\PartnerModel::class),
            model(\App\Models\EkstrakurikulerModel::class),
        );
    }

    public static function kontakService($getShared = true): \App\Services\KontakService
    {
        if ($getShared) {
            return static::getSharedInstance('kontakService');
        }

        return new \App\Services\KontakService(
            model(\App\Models\PesanKontakModel::class),
            model(\App\Models\NotifikasiModel::class),
        );
    }

    public static function ppdbService($getShared = true): \App\Services\PpdbService
    {
        if ($getShared) {
            return static::getSharedInstance('ppdbService');
        }

        return new \App\Services\PpdbService(
            model(\App\Models\PendaftarPpdbModel::class),
            model(\App\Models\PengaturanPpdbModel::class),
            model(\App\Models\NotifikasiModel::class),
        );
    }

    public static function passwordService($getShared = true): \App\Services\PasswordService
    {
        if ($getShared) {
            return static::getSharedInstance('passwordService');
        }

        return new \App\Services\PasswordService(
            model(\App\Models\UserModel::class),
            model(\App\Models\AuditLogModel::class),
        );
    }

    public static function jurusanService($getShared = true): \App\Services\JurusanService
    {
        if ($getShared) {
            return static::getSharedInstance('jurusanService');
        }

        return new \App\Services\JurusanService(
            model(\App\Models\JurusanModel::class),
            static::fileUploadService(),
            static::slugService(),
            \Config\Database::connect(),
        );
    }

    public static function bkArtikelService($getShared = true): \App\Services\BkArtikelService
    {
        if ($getShared) {
            return static::getSharedInstance('bkArtikelService');
        }

        return new \App\Services\BkArtikelService(
            model(\App\Models\BkArtikelModel::class),
            static::fileUploadService(),
            static::slugService(),
            \Config\Database::connect(),
        );
    }

    public static function unduhanService($getShared = true): \App\Services\UnduhanService
    {
        if ($getShared) {
            return static::getSharedInstance('unduhanService');
        }

        return new \App\Services\UnduhanService(
            model(\App\Models\UnduhanModel::class),
            static::fileUploadService(),
            \Config\Database::connect(),
        );
    }

    public static function importSiswaService($getShared = true): \App\Services\ImportSiswaService
    {
        if ($getShared) {
            return static::getSharedInstance('importSiswaService');
        }

        return new \App\Services\ImportSiswaService(
            model(\App\Models\ImportLogModel::class),
            model(\App\Models\SiswaModel::class),
            model(\App\Models\JurusanModel::class),
            static::siswaService(),
        );
    }

    public static function beritaService($getShared = true): \App\Services\BeritaService
    {
        if ($getShared) {
            return static::getSharedInstance('beritaService');
        }

        return new \App\Services\BeritaService(
            model(\App\Models\BeritaModel::class),
            model(\App\Models\AuditLogModel::class),
            static::fileUploadService(),
            static::slugService(),
        );
    }

    public static function guruService($getShared = true): \App\Services\GuruService
    {
        if ($getShared) {
            return static::getSharedInstance('guruService');
        }

        return new \App\Services\GuruService(
            model(\App\Models\GuruModel::class),
            static::fileUploadService(),
        );
    }

    public static function galeriService($getShared = true): \App\Services\GaleriService
    {
        if ($getShared) {
            return static::getSharedInstance('galeriService');
        }

        return new \App\Services\GaleriService(
            model(\App\Models\GaleriModel::class),
            static::fileUploadService(),
        );
    }

    public static function partnerService($getShared = true): \App\Services\PartnerService
    {
        if ($getShared) {
            return static::getSharedInstance('partnerService');
        }

        return new \App\Services\PartnerService(
            model(\App\Models\PartnerModel::class),
            static::fileUploadService(),
            static::slugService(),
        );
    }

    public static function ekstrakurikulerService($getShared = true): \App\Services\EkstrakurikulerService
    {
        if ($getShared) {
            return static::getSharedInstance('ekstrakurikulerService');
        }

        return new \App\Services\EkstrakurikulerService(
            model(\App\Models\EkstrakurikulerModel::class),
            static::fileUploadService(),
        );
    }

    public static function prestasiService($getShared = true): \App\Services\PrestasiService
    {
        if ($getShared) {
            return static::getSharedInstance('prestasiService');
        }

        return new \App\Services\PrestasiService(
            model(\App\Models\PrestasiModel::class),
            static::fileUploadService(),
        );
    }

    public static function organisasiService($getShared = true): \App\Services\OrganisasiService
    {
        if ($getShared) {
            return static::getSharedInstance('organisasiService');
        }

        return new \App\Services\OrganisasiService(
            model(\App\Models\OrganisasiModel::class),
            model(\App\Models\AnggotaOrganisasiModel::class),
            static::fileUploadService(),
        );
    }

    public static function anggotaOrganisasiService($getShared = true): \App\Services\AnggotaOrganisasiService
    {
        if ($getShared) {
            return static::getSharedInstance('anggotaOrganisasiService');
        }

        return new \App\Services\AnggotaOrganisasiService(
            model(\App\Models\AnggotaOrganisasiModel::class),
            model(\App\Models\OrganisasiModel::class),
            static::fileUploadService(),
        );
    }

    public static function profilSekolahService($getShared = true): \App\Services\ProfilSekolahService
    {
        if ($getShared) {
            return static::getSharedInstance('profilSekolahService');
        }

        return new \App\Services\ProfilSekolahService(
            model(\App\Models\ProfilKontenModel::class),
            static::fileUploadService(),
        );
    }

    public static function pengaturanService($getShared = true): \App\Services\PengaturanService
    {
        if ($getShared) {
            return static::getSharedInstance('pengaturanService');
        }

        return new \App\Services\PengaturanService(
            model(\App\Models\PengaturanModel::class),
            model(\App\Models\AuditLogModel::class),
            static::fileUploadService(),
        );
    }

    public static function userService($getShared = true): \App\Services\UserService
    {
        if ($getShared) {
            return static::getSharedInstance('userService');
        }

        return new \App\Services\UserService(
            model(\App\Models\UserModel::class),
            model(\App\Models\AuditLogModel::class),
        );
    }

    public static function pklService($getShared = true): \App\Services\PklService
    {
        if ($getShared) {
            return static::getSharedInstance('pklService');
        }

        return new \App\Services\PklService(
            model(\App\Models\PklPenempatanModel::class),
            model(\App\Models\SiswaModel::class),
            model(\App\Models\GuruModel::class),
        );
    }

    public static function ppdbAdminService($getShared = true): \App\Services\PpdbAdminService
    {
        if ($getShared) {
            return static::getSharedInstance('ppdbAdminService');
        }

        return new \App\Services\PpdbAdminService(
            model(\App\Models\PendaftarPpdbModel::class),
            model(\App\Models\PengaturanPpdbModel::class),
        );
    }

    /* public static function example

    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
}
