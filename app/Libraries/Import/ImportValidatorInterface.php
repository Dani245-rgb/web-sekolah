<?php

namespace App\Libraries\Import;

interface ImportValidatorInterface
{
    /**
     * Validasi 1 baris data mentah. Return ImportRow dengan status & alasan terisi.
     */
    public function validateRow(int $nomorBaris, array $rawRow): ImportRow;
}