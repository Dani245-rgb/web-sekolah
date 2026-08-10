<?php

namespace App\Libraries\Import;

class ImportRow
{
    public int $nomorBaris;
    public array $data;                  // data mentah dari Excel
    public string $status;                 // 'valid' | 'ditimpa' | 'tidak_berubah' | 'konflik' | 'gagal'
    public ?string $alasanGagal = null;
    public array $dataLama = [];            // isi kalau status='ditimpa'/'konflik', buat preview & audit
    public array $dataBaru = [];
    public ?float $nilaiSnapshot = null;    // nilai di database saat template di-download — dipakai deteksi konflik

    public function __construct(int $nomorBaris, array $data)
    {
        $this->nomorBaris = $nomorBaris;
        $this->data = $data;
    }
}