<?php

namespace App\Libraries\Import;

class ImportResult
{
    public int $totalValid = 0;
    public int $totalDitimpa = 0;
    public int $totalTidakBerubah = 0;
    public int $totalKonflik = 0;
    public int $totalGagal = 0;
    public array $rows = [];              // array of ImportRow
    public ?int $idImportLog = null;       // diisi setelah confirm()

    public function ringkasan(): array
    {
        return [
            'valid'         => $this->totalValid,
            'ditimpa'       => $this->totalDitimpa,
            'tidak_berubah' => $this->totalTidakBerubah,
            'konflik'       => $this->totalKonflik,
            'gagal'         => $this->totalGagal,
        ];
    }
}