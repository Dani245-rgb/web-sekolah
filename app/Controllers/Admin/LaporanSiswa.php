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
        $status = $this->request->getGet('status');

        $builder = $this->siswaModel->select('siswa.*, kelas.nama_kelas, kelas.tingkat, kelas.jurusan')
            ->join('kelas_siswa', 'kelas_siswa.id_siswa = siswa.id_siswa', 'left')
            ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas', 'left');

        if (!empty($status)) {
            $builder->where('siswa.status', $status);
        }

        $siswa = $builder->orderBy('siswa.nama', 'ASC')->findAll();

        $totalPerStatus = [];
        $totalPerJurusan = [];
        $totalPerGender = ['L' => 0, 'P' => 0];

        foreach ($siswa as $s) {
            $st = $s['status'] ?? '-';
            $totalPerStatus[$st] = ($totalPerStatus[$st] ?? 0) + 1;

            $jur = $s['jurusan'] ?? 'Belum Ada Kelas';
            $totalPerJurusan[$jur] = ($totalPerJurusan[$jur] ?? 0) + 1;

            if (isset($totalPerGender[$s['jenis_kelamin']])) {
                $totalPerGender[$s['jenis_kelamin']]++;
            }
        }

        $data['siswa']           = $siswa;
        $data['totalPerStatus']  = $totalPerStatus;
        $data['totalPerJurusan'] = $totalPerJurusan;
        $data['totalPerGender']  = $totalPerGender;
        $data['totalSiswa']      = count($siswa);
        $data['statusFilter']    = $status;

        return view('admin/laporan_siswa/index', $data);
    }

    public function exportExcel()
    {
        $status = $this->request->getGet('status');

        $builder = $this->siswaModel->select('siswa.*, kelas.nama_kelas, kelas.tingkat, kelas.jurusan')
            ->join('kelas_siswa', 'kelas_siswa.id_siswa = siswa.id_siswa', 'left')
            ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas', 'left');

        if (!empty($status)) {
            $builder->where('siswa.status', $status);
        }

        $siswa = $builder->orderBy('siswa.nama', 'ASC')->findAll();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Siswa');

        $header = ['No', 'NIS', 'NISN', 'Nama', 'Jenis Kelamin', 'Kelas', 'Jurusan', 'Status'];
        $sheet->fromArray($header, null, 'A1');

        helper('excel');

        $row = 2;
        foreach ($siswa as $i => $s) {
            $sheet->fromArray([
                $i + 1,
                $s['nis'],
                $s['nisn'],
                sanitize_excel_cell($s['nama']),
                $s['jenis_kelamin'],
                sanitize_excel_cell($s['nama_kelas'] ?? '-'),
                sanitize_excel_cell($s['jurusan'] ?? '-'),
                $s['status'],
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'H') as $col) {
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