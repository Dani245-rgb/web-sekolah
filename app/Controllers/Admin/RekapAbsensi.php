<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AbsensiDetailModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapAbsensi extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();
        $data['kelas'] = $kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();

        $idKelas = $this->request->getGet('id_kelas');
        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');

        $absensiDetailModel = new AbsensiDetailModel();
        $data['hasil']   = $idKelas ? $absensiDetailModel->getByKelasTanggal($idKelas, $tanggal) : [];
        $data['idKelas'] = $idKelas;
        $data['tanggal'] = $tanggal;

        return view('admin/rekap-absensi/index', $data);
    }

    public function siswa()
    {
        $keyword = $this->request->getGet('keyword');

        $siswaModel = new SiswaModel();
        $builder = $siswaModel->select('id_siswa, nis, nama, status')->orderBy('nama', 'ASC');
        if ($keyword) {
            $builder->groupStart()->like('nama', $keyword)->orLike('nis', $keyword)->groupEnd();
        }

        $data['siswa']   = $builder->findAll();
        $data['keyword'] = $keyword;

        return view('admin/rekap-absensi/siswa', $data);
    }

    public function siswaDetail($idSiswa)
    {
        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->find($idSiswa);
        if (!$siswa) {
            return redirect()->to('/admin/rekap-absensi/siswa')->with('errors', ['404' => 'Siswa tidak ditemukan.']);
        }

        $tglMulai   = $this->request->getGet('tgl_mulai') ?: date('Y-m-01');
        $tglSelesai = $this->request->getGet('tgl_selesai') ?: date('Y-m-d');

        $absensiDetailModel = new AbsensiDetailModel();
        $rekap  = $absensiDetailModel->rekapPerSiswa($idSiswa, $tglMulai, $tglSelesai);
        $detail = $absensiDetailModel->getBySiswaRange($idSiswa, $tglMulai, $tglSelesai);

        $data['siswa']      = $siswa;
        $data['rekap']      = $rekap;
        $data['detail']     = $detail;
        $data['tglMulai']   = $tglMulai;
        $data['tglSelesai'] = $tglSelesai;

        return view('admin/rekap-absensi/siswa-detail', $data);
    }

    /**
     * Export PDF: rekap absensi 1 kelas pada 1 tanggal (mode index())
     */
    public function exportPdfKelas()
    {
        $idKelas = $this->request->getGet('id_kelas');
        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');

        if (!$idKelas) {
            return redirect()->to('/admin/rekap-absensi')->with('errors', ['kelas' => 'Pilih kelas terlebih dahulu sebelum export.']);
        }

        $kelasModel = new KelasModel();
        $kelas = $kelasModel->find($idKelas);

        $absensiDetailModel = new AbsensiDetailModel();
        $hasil = $absensiDetailModel->getByKelasTanggal($idKelas, $tanggal);

        $html = view('admin/rekap-absensi/pdf-kelas', [
            'kelas'   => $kelas,
            'tanggal' => $tanggal,
            'hasil'   => $hasil,
        ]);

        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->SetTitle('Rekap Absensi Kelas');
        $mpdf->WriteHTML($html);
        $mpdf->Output('rekap-absensi-kelas_' . date('Y-m-d', strtotime($tanggal)) . '.pdf', 'D');
    }

    /**
     * Export PDF: riwayat absensi 1 siswa dalam rentang tanggal (mode siswaDetail())
     */
    public function exportPdfSiswa($idSiswa)
    {
        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->find($idSiswa);
        if (!$siswa) {
            return redirect()->to('/admin/rekap-absensi/siswa')->with('errors', ['404' => 'Siswa tidak ditemukan.']);
        }

        $tglMulai   = $this->request->getGet('tgl_mulai') ?: date('Y-m-01');
        $tglSelesai = $this->request->getGet('tgl_selesai') ?: date('Y-m-d');

        $absensiDetailModel = new AbsensiDetailModel();
        $rekap  = $absensiDetailModel->rekapPerSiswa($idSiswa, $tglMulai, $tglSelesai);
        $detail = $absensiDetailModel->getBySiswaRange($idSiswa, $tglMulai, $tglSelesai);

        $html = view('admin/rekap-absensi/pdf-siswa', [
            'siswa'      => $siswa,
            'rekap'      => $rekap,
            'detail'     => $detail,
            'tglMulai'   => $tglMulai,
            'tglSelesai' => $tglSelesai,
        ]);

        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->SetTitle('Riwayat Absensi Siswa');
        $mpdf->WriteHTML($html);
        $mpdf->Output('riwayat-absensi_' . str_replace(' ', '-', $siswa['nama']) . '.pdf', 'D');
    }

    /**
     * Export Excel: rekap absensi 1 kelas pada 1 tanggal
     */
    public function exportExcelKelas()
    {
        $idKelas = $this->request->getGet('id_kelas');
        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');

        if (!$idKelas) {
            return redirect()->to('/admin/rekap-absensi')->with('errors', ['kelas' => 'Pilih kelas terlebih dahulu sebelum export.']);
        }

        $kelasModel = new KelasModel();
        $kelas = $kelasModel->find($idKelas);

        $absensiDetailModel = new AbsensiDetailModel();
        $hasil = $absensiDetailModel->getByKelasTanggal($idKelas, $tanggal);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Absensi');

        $sheet->setCellValue('A1', 'Kelas');
        $sheet->setCellValue('B1', $kelas['nama_kelas'] ?? '-');
        $sheet->setCellValue('A2', 'Tanggal');
        $sheet->setCellValue('B2', date('d-m-Y', strtotime($tanggal)));
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);

        $headerRow = 4;
        $sheet->fromArray(['No', 'Jam', 'Mapel', 'NIS', 'Nama', 'Status', 'Keterangan'], null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EEF1FF');

        $row = $headerRow + 1;
        foreach ($hasil as $i => $h) {
            $jam = substr($h['jam_mulai'], 0, 5) . '-' . substr($h['jam_selesai'], 0, 5);
            $sheet->fromArray([
                $i + 1,
                $jam,
                $h['nama_mapel'],
                $h['nis'],
                $h['nama'],
                $h['status'],
                $h['keterangan'] ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'rekap-absensi-kelas_' . date('Y-m-d', strtotime($tanggal)) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Export Excel: riwayat absensi 1 siswa dalam rentang tanggal
     */
    public function exportExcelSiswa($idSiswa)
    {
        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->find($idSiswa);
        if (!$siswa) {
            return redirect()->to('/admin/rekap-absensi/siswa')->with('errors', ['404' => 'Siswa tidak ditemukan.']);
        }

        $tglMulai   = $this->request->getGet('tgl_mulai') ?: date('Y-m-01');
        $tglSelesai = $this->request->getGet('tgl_selesai') ?: date('Y-m-d');

        $absensiDetailModel = new AbsensiDetailModel();
        $rekap  = $absensiDetailModel->rekapPerSiswa($idSiswa, $tglMulai, $tglSelesai);
        $detail = $absensiDetailModel->getBySiswaRange($idSiswa, $tglMulai, $tglSelesai);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Absensi');

        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', $siswa['nama']);
        $sheet->setCellValue('A2', 'NIS');
        $sheet->setCellValue('B2', $siswa['nis']);
        $sheet->setCellValue('A3', 'Periode');
        $sheet->setCellValue('B3', date('d-m-Y', strtotime($tglMulai)) . ' s/d ' . date('d-m-Y', strtotime($tglSelesai)));
        $sheet->setCellValue('A4', 'Hadir');
        $sheet->setCellValue('B4', $rekap['Hadir'] ?? 0);
        $sheet->setCellValue('A5', 'Izin');
        $sheet->setCellValue('B5', $rekap['Izin'] ?? 0);
        $sheet->setCellValue('A6', 'Sakit');
        $sheet->setCellValue('B6', $rekap['Sakit'] ?? 0);
        $sheet->setCellValue('A7', 'Alfa');
        $sheet->setCellValue('B7', $rekap['Alfa'] ?? 0);
        $sheet->getStyle('A1:A7')->getFont()->setBold(true);

        $headerRow = 9;
        $sheet->fromArray(['No', 'Tanggal', 'Mapel', 'Status', 'Keterangan'], null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:E{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:E{$headerRow}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EEF1FF');

        $row = $headerRow + 1;
        foreach ($detail as $i => $d) {
            $sheet->fromArray([
                $i + 1,
                date('d-m-Y', strtotime($d['tanggal'])),
                $d['nama_mapel'],
                $d['status'],
                $d['keterangan'] ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'riwayat-absensi_' . str_replace(' ', '-', $siswa['nama']) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
