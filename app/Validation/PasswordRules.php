<?php

namespace App\Validation;

class PasswordRules
{
    /**
     * Password wajib: minimal 8 karakter, ada huruf besar, dan ada angka.
     */
    public function strongPassword(string $str, ?string &$error = null): bool
    {
        if (strlen($str) < 8) {
            $error = 'Password minimal 8 karakter.';
            return false;
        }

        if (!preg_match('/[A-Z]/', $str)) {
            $error = 'Password harus mengandung minimal 1 huruf besar.';
            return false;
        }

        if (!preg_match('/[0-9]/', $str)) {
            $error = 'Password harus mengandung minimal 1 angka.';
            return false;
        }

        return true;
    }
}