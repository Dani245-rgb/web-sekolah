<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengaturanNilaiModel;
use App\Models\KomponenNilaiModel;
use App\Models\NilaiSiswaModel;
use App\Models\SemesterModel;
use App\Models\KelasModel;
use App\Models\MapelModel;
use App\Models\GuruModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RekapNilai extends BaseController
{
    public function index()
    {
        $semesterModel = new SemesterModel();
        $semesterAktif = $semesterModel->where('status', 'Aktif')->first();

        if (!$semesterAktif) {
            return redirect()->to('/admin/dashboard')->with('errors', ['semester' => 'Belum ada Semester Aktif.']);
        }

        $filter = [
            'id_kelas' => $this->request->getGet('id_kelas'),
            'id_mapel' => $this->request->getGet('id_mapel'),
            'id_guru'  => $this->request->getGet('id_guru'),
        ];

        $db = \Config\Database::connect();
        $builder = $db->table('pengaturan_nilai pn')
            ->select('pn.*, guru.nama as nama_guru, kelas.nama_kelas, mapel.nama_mapel')
            ->join('guru', 'guru.id_guru = pn.id_guru')
            ->join('kelas', 'kelas.id_kelas = pn.id_kelas')
            ->join('mapel', 'mapel.id_mapel = pn.id_mapel')
            ->where('pn.id_semester', $semesterAktif['id_semester']);

        if (!empty($filter['id_kelas'])) $builder->where('pn.id_kelas', $filter['id_kelas']);
        if (!empty($filter['id_mapel'])) $builder->where('pn.id_mapel', $filter['id_mapel']);
        if (!empty($filter['id_guru']))  $builder->where('pn.id_guru', $filter['id_guru']);

        $daftarPengaturan = $builder->get()->getResultArray();

        $kelasModel = new KelasModel();
        $mapelModel = new MapelModel();
        $guruModel  = new GuruModel();

        $data['daftarPengaturan'] = $daftarPengaturan;
        $data['semesterAktif']    = $semesterAktif;
        $data['filter']           = $filter;
        $data['kelasList']        = $kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();
        $data['mapelList']        = $mapelModel->where('status', 'Aktif')->orderBy('nama_mapel', 'ASC')->findAll();
        $data['guruList']         = $guruModel->orderBy('nama', 'ASC')->findAll();

        return view('admin/nilai/rekap', $data);
    }

    public function detail($id_pengaturan)
    {
        $pengaturanModel = new PengaturanNilaiModel();
        $komponenModel   = new KomponenNilaiModel();

        $pengaturan = $pengaturanModel->find($id_pengaturan);
        if (!$pengaturan) {
            return redirect()->to('/admin/nilai/rekap')->with('errors', ['404' => 'Pengaturan nilai tidak ditemukan.']);
        }

        $komponenList = $komponenModel->where('id_pengaturan', $id_pengaturan)->findAll();
        $bobotMap     = array_column($komponenList, 'bobot', 'id_komponen');

        $db = \Config\Database::connect();
        $rows = $db->table('nilai_siswa ns')
            ->select('ns.*, siswa.nama as nama_siswa')
            ->join('siswa', 'siswa.id_siswa = ns.id_siswa')
            ->whereIn('ns.id_komponen', array_keys($bobotMap))
            ->get()->getResultArray();

        $perSiswa = [];
        foreach ($rows as $row) {
            $perSiswa[$row['id_siswa']]['nama_siswa'] = $row['nama_siswa'];
            $perSiswa[$row['id_siswa']]['nilai'][] = $row;
        }

        $hasil = [];
        foreach ($perSiswa as $data) {
            $nilaiAkhir = 0;
            foreach ($data['nilai'] as $row) {
                $nilaiAkhir += $row['nilai'] * ($bobotMap[$row['id_komponen']] ?? 0) / 100;
            }
            $hasil[] = [
                'nama_siswa'  => $data['nama_siswa'],
                'nilai_akhir' => round($nilaiAkhir, 2),
                'status'      => $nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas',
            ];
        }

        usort($hasil, fn($a, $b) => strcmp($a['nama_siswa'], $b['nama_siswa']));

        return view('admin/nilai/detail', compact('pengaturan', 'hasil'));
    }

    public function exportPdf($id_pengaturan)
    {
        $pengaturanModel = new PengaturanNilaiModel();
        $komponenModel   = new KomponenNilaiModel();

        $db = \Config\Database::connect();
        $pengaturan = $db->table('pengaturan_nilai pn')
            ->select('pn.*, guru.nama as nama_guru, kelas.nama_kelas, mapel.nama_mapel')
            ->join('guru', 'guru.id_guru = pn.id_guru')
            ->join('kelas', 'kelas.id_kelas = pn.id_kelas')
            ->join('mapel', 'mapel.id_mapel = pn.id_mapel')
            ->where('pn.id_pengaturan', $id_pengaturan)
            ->get()->getRowArray();

        if (!$pengaturan) {
            return redirect()->to('/admin/nilai/rekap')->with('errors', ['404' => 'Pengaturan nilai tidak ditemukan.']);
        }

       $komponenList = $komponenModel->where('id_pengaturan', $id_pengaturan)->findAll();
        $bobotMap     = array_column($komponenList, 'bobot', 'id_komponen');

        $rows = $db->table('nilai_siswa ns')
            ->select('ns.*, siswa.nama as nama_siswa')
            ->join('siswa', 'siswa.id_siswa = ns.id_siswa')
            ->whereIn('ns.id_komponen', array_keys($bobotMap))
            ->get()->getResultArray();

        $perSiswa = [];
        foreach ($rows as $row) {
            $perSiswa[$row['id_siswa']]['nama_siswa'] = $row['nama_siswa'];
            $perSiswa[$row['id_siswa']]['nilai'][] = $row;
        }

        $hasil = [];
        foreach ($perSiswa as $data) {
            $nilaiAkhir = 0;
            foreach ($data['nilai'] as $row) {
                $nilaiAkhir += $row['nilai'] * ($bobotMap[$row['id_komponen']] ?? 0) / 100;
            }
            $hasil[] = [
                'nama_siswa'  => $data['nama_siswa'],
                'nilai_akhir' => round($nilaiAkhir, 2),
                'status'      => $nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas',
            ];
        }

        usort($hasil, fn($a, $b) => strcmp($a['nama_siswa'], $b['nama_siswa']));

        $html = view('admin/nilai/pdf', [
            'pengaturan' => $pengaturan,
            'hasil'      => $hasil,
        ]);

       $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->SetTitle('Rekap Nilai');
        $mpdf->WriteHTML($html);
        $mpdf->Output('rekap-nilai_' . $id_pengaturan . '.pdf', 'D');
    }

    public function exportExcel($id_pengaturan)
    {
        $pengaturanModel = new PengaturanNilaiModel();
        $komponenModel   = new KomponenNilaiModel();

        $db = \Config\Database::connect();
        $pengaturan = $db->table('pengaturan_nilai pn')
            ->select('pn.*, guru.nama as nama_guru, kelas.nama_kelas, mapel.nama_mapel')
            ->join('guru', 'guru.id_guru = pn.id_guru')
            ->join('kelas', 'kelas.id_kelas = pn.id_kelas')
            ->join('mapel', 'mapel.id_mapel = pn.id_mapel')
            ->where('pn.id_pengaturan', $id_pengaturan)
            ->get()->getRowArray();

        if (!$pengaturan) {
            return redirect()->to('/admin/nilai/rekap')->with('errors', ['404' => 'Pengaturan nilai tidak ditemukan.']);
        }

        $komponenList = $komponenModel->where('id_pengaturan', $id_pengaturan)->findAll();
        $bobotMap     = array_column($komponenList, 'bobot', 'id_komponen');

        $rows = $db->table('nilai_siswa ns')
            ->select('ns.*, siswa.nama as nama_siswa')
            ->join('siswa', 'siswa.id_siswa = ns.id_siswa')
            ->whereIn('ns.id_komponen', array_keys($bobotMap))
            ->get()->getResultArray();

        $perSiswa = [];
        foreach ($rows as $row) {
            $perSiswa[$row['id_siswa']]['nama_siswa'] = $row['nama_siswa'];
            $perSiswa[$row['id_siswa']]['nilai'][] = $row;
        }

        $hasil = [];
        foreach ($perSiswa as $data) {
            $nilaiAkhir = 0;
            foreach ($data['nilai'] as $row) {
                $nilaiAkhir += $row['nilai'] * ($bobotMap[$row['id_komponen']] ?? 0) / 100;
            }
            $hasil[] = [
                'nama_siswa'  => $data['nama_siswa'],
                'nilai_akhir' => round($nilaiAkhir, 2),
                'status'      => $nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas',
            ];
        }

        usort($hasil, fn($a, $b) => strcmp($a['nama_siswa'], $b['nama_siswa']));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');

        $sheet->setCellValue('A1', 'Kelas');
        $sheet->setCellValue('B1', $pengaturan['nama_kelas']);
        $sheet->setCellValue('A2', 'Mapel');
        $sheet->setCellValue('B2', $pengaturan['nama_mapel']);
        $sheet->setCellValue('A3', 'Guru');
        $sheet->setCellValue('B3', $pengaturan['nama_guru']);
        $sheet->setCellValue('A4', 'KKM');
        $sheet->setCellValue('B4', $pengaturan['kkm']);
        $sheet->getStyle('A1:A4')->getFont()->setBold(true);

        $headerRow = 6;
        $sheet->fromArray(['No', 'Nama Siswa', 'Nilai Akhir', 'Status'], null, "A{$headerRow}");
        $sheet->getStyle("A{$headerRow}:D{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:D{$headerRow}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('EEF1FF');

        $row = $headerRow + 1;
        foreach ($hasil as $i => $h) {
            $sheet->fromArray([$i + 1, $h['nama_siswa'], $h['nilai_akhir'], $h['status']], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'rekap-nilai_' . $id_pengaturan . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}