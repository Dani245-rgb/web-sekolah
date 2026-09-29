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
