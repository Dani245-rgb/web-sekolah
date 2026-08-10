<?php

namespace App\Libraries\Import;

use PhpOffice\PhpSpreadsheet\IOFactory;

abstract class ImportService
{
    protected ImportValidatorInterface $validator;

    public function __construct(ImportValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Nama jenis import ini, harus match ENUM di kolom import_log.jenis_import
     * Contoh: 'nilai'
     */
    abstract public function jenisImport(): string;

    /**
     * Baca file Excel jadi array baris mentah (asosiatif per kolom).
     * Turunan boleh override kalau mapping kolomnya beda-beda.
     */
    protected function parse(string $pathFile): array
    {
        $spreadsheet = IOFactory::load($pathFile);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true); // key kolom pakai huruf (A, B, C...)

        $header = array_shift($rows); // baris pertama = header, dibuang
        return $rows;
    }

    /**
     * Jalankan validasi ke semua baris, hasilkan ImportResult (belum disimpan ke DB).
     */
    public function preview(string $pathFile): ImportResult
    {
        $rawRows = $this->parse($pathFile);
        $result = new ImportResult();

        $nomorBaris = 2; // baris 1 = header
        foreach ($rawRows as $rawRow) {
            $importRow = $this->validator->validateRow($nomorBaris, $rawRow);
            $result->rows[] = $importRow;

            match ($importRow->status) {
                'valid'         => $result->totalValid++,
                'ditimpa'       => $result->totalDitimpa++,
                'tidak_berubah' => $result->totalTidakBerubah++,
                'konflik'       => $result->totalKonflik++,
                'gagal'         => $result->totalGagal++,
            };

            $nomorBaris++;
        }

        return $result;
    }

    /**
     * Simpan hasil import ke DB. Turunan HARUS override ini untuk panggil
     * Importer + Repository spesifik modul — ImportService sendiri tidak
     * boleh melakukan query database.
     */
    abstract public function confirm(
        ImportResult $result,
        bool $lewatiBarisGagal,
        int $idUser,
        string $namaFile,
        ?int $idReferensi = null
    ): ImportResult;

    // rollback() SENGAJA TIDAK ADA DI SINI.
    // Rollback tidak butuh validator (ImportValidatorInterface), jadi dipisah
    // ke service sendiri (contoh: NilaiRollbackService) yang constructor-nya
    // cuma minta Repository — bukan numpang di constructor ImportService.
}
