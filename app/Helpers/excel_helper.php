<?php

if (!function_exists('sanitize_excel_cell')) {
    function sanitize_excel_cell($value): string
    {
        $value = (string) $value;
        if ($value !== '' && preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value; // prefix apostrophe → Excel baca sebagai text, bukan formula
        }
        return $value;
    }
}