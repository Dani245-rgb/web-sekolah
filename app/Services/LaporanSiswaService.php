<?php

namespace App\Services;

use App\Models\SiswaModel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Data dan file Excel untuk halaman Laporan Siswa.
 */
class LaporanSiswaService
{
    public function __construct(
        protected SiswaModel $siswaModel,
    ) {
    }

    /**
     * Semua siswa aktif, urut nama.
     */
    public function ambilSiswa(): array
    {
        return $this->siswaModel->orderBy('nama', 'ASC')->findAll();
    }

    /**
     * Jumlah siswa per jenis kelamin, contoh: ['L' => 120, 'P' => 98].
     */
    public function hitungPerGender(array $siswa): array
    {
        $total = ['L' => 0, 'P' => 0];

        foreach ($siswa as $s) {
            if (isset($total[$s['jenis_kelamin']])) {
                $total[$s['jenis_kelamin']]++;
            }
        }

        return $total;
    }

    /**
     * Bikin file .xlsx, return isi filenya (binary string).
     */
    public function buatExcel(array $siswa): string
    {
        helper('excel');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Siswa');

        $sheet->fromArray(['No', 'NIS', 'NISN', 'Nama', 'Jenis Kelamin'], null, 'A1');

        $row = 2;
        foreach ($siswa as $i => $s) {
            $sheet->setCellValue("A{$row}", $i + 1);

            // Ditulis sebagai TEKS supaya nol di depan dan angka panjang (NISN) tidak rusak
            $sheet->setCellValueExplicit("B{$row}", (string) sanitize_excel_cell((string) ($s['nis'] ?? '')), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$row}", (string) sanitize_excel_cell((string) ($s['nisn'] ?? '')), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$row}", (string) sanitize_excel_cell((string) ($s['nama'] ?? '')), DataType::TYPE_STRING);

            $sheet->setCellValue("E{$row}", $s['jenis_kelamin']);
            $row++;
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');

        return (string) ob_get_clean();
    }
}