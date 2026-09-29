<?php

if (!function_exists('rapikan_teks')) {
    /**
     * Buang spasi di pinggir dan ubah spasi/tab/baris-baru beruntun jadi satu spasi.
     */
    function rapikan_teks($teks): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $teks));
    }
}