<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;

class LaporanSiswa extends BaseController
{
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
    }

    public function index()
    {
        $siswa = $this->siswaModel->orderBy('nama', 'ASC')->findAll();

        $totalPerGender = ['L' => 0, 'P' => 0];
        foreach ($siswa as $s) {
            if (isset($totalPerGender[$s['jenis_kelamin']])) {
                $totalPerGender[$s['jenis_kelamin']]++;
            }
        }

        $data['siswa']          = $siswa;
        $data['totalPerGender'] = $totalPerGender;
        $data['totalSiswa']     = count($siswa);

        return view('admin/laporan_siswa/index', $data);
    }

    public function exportExcel()
    {
        $siswa = $this->siswaModel->orderBy('nama', 'ASC')->findAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Siswa');

        $header = ['No', 'NIS', 'NISN', 'Nama', 'Jenis Kelamin'];
        $sheet->fromArray($header, null, 'A1');

        helper('excel');

        $row = 2;
        foreach ($siswa as $i => $s) {
            $sheet->fromArray([
                $i + 1,
                sanitize_excel_cell($s['nis']),
                sanitize_excel_cell($s['nisn']),
                sanitize_excel_cell($s['nama']),
                $s['jenis_kelamin'],
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'laporan_siswa_' . date('Y-m-d_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}