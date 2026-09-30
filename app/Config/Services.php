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
