<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JadwalModel;
use App\Models\KelasModel;
use App\Models\MapelModel;
use App\Models\GuruModel;
use App\Models\RuanganModel;
use App\Models\SemesterModel;
use App\Models\JurusanModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Jadwal extends BaseController
{
    protected JadwalModel $jadwalModel;
    protected KelasModel $kelasModel;
    protected MapelModel $mapelModel;
    protected GuruModel $guruModel;
    protected RuanganModel $ruanganModel;
    protected SemesterModel $semesterModel;
    protected JurusanModel $jurusanModel;

    public function __construct()
    {
        $this->jadwalModel   = new JadwalModel();
        $this->kelasModel    = new KelasModel();
        $this->mapelModel    = new MapelModel();
        $this->guruModel     = new GuruModel();
        $this->ruanganModel  = new RuanganModel();
        $this->semesterModel = new SemesterModel();
        $this->jurusanModel  = new JurusanModel();
    }

    public function index()
    {
        $semesterAktif = $this->semesterModel->getActive();
        if (!$semesterAktif) {
            return redirect()->to('/admin/dashboard')->with('errors', ['semester' => 'Belum ada Semester Aktif.']);
        }

        $filter = [
            'id_jurusan' => $this->request->getGet('id_jurusan'),
            'id_kelas'   => $this->request->getGet('id_kelas'),
            'hari'       => $this->request->getGet('hari'),
            'keyword'    => $this->request->getGet('keyword'),
        ];

        $data['jadwal']        = $this->jadwalModel->getAllWithRelasi($semesterAktif['id_semester'], $filter);
        $data['semesterAktif'] = $semesterAktif;
        $data['jurusan']       = $this->jurusanModel->orderBy('nama_jurusan', 'ASC')->findAll();
        $data['kelas']         = $this->kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();
        $data['filter']        = $filter;

        return view('admin/jadwal/index', $data);
    }

    // ---------- CRUD manual ----------

    public function create()
    {
        $data['kelas']   = $this->kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();
        $data['mapel']   = $this->mapelModel->where('status', 'Aktif')->orderBy('nama_mapel', 'ASC')->findAll();
        $data['guru']    = $this->guruModel->orderBy('nama', 'ASC')->findAll();
        $data['ruangan'] = $this->ruanganModel->where('status', 'Aktif')->orderBy('nama_ruangan', 'ASC')->findAll();

        return view('admin/jadwal/create', $data);
    }

    public function store()
    {
        $rules = [
            'id_kelas'    => 'required|is_natural_no_zero',
            'id_mapel'    => 'required|is_natural_no_zero',
            'id_guru'     => 'required|is_natural_no_zero',
            'id_ruangan'  => 'required|is_natural_no_zero',
            'hari'        => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu]',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $semesterAktif = $this->semesterModel->getActive();
        if (!$semesterAktif) {
            return redirect()->back()->with('errors', ['semester' => 'Belum ada Semester Aktif.']);
        }

        $data = [
            'id_semester' => $semesterAktif['id_semester'],
            'id_kelas'    => $this->request->getPost('id_kelas'),
            'id_mapel'    => $this->request->getPost('id_mapel'),
            'id_guru'     => $this->request->getPost('id_guru'),
            'id_ruangan'  => $this->request->getPost('id_ruangan'),
            'hari'        => $this->request->getPost('hari'),
            'jam_mulai'   => $this->request->getPost('jam_mulai'),
            'jam_selesai' => $this->request->getPost('jam_selesai'),
        ];

        if (strtotime($data['jam_selesai']) <= strtotime($data['jam_mulai'])) {
            return redirect()->back()->withInput()->with('errors', ['jam' => 'Jam selesai harus lebih besar dari jam mulai.']);
        }

        $bentrok = $this->jadwalModel->cekBentrok($data);
        if (!empty($bentrok)) {
            return redirect()->back()->withInput()->with('errors', ['bentrok' => implode(' ', $bentrok)]);
        }

        $this->jadwalModel->insert($data);

        return redirect()->to('/admin/jadwal')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit($id)
{
    $jadwal = $this->jadwalModel->find($id);
    if (!$jadwal) {
        return redirect()->to('/admin/jadwal')->with('errors', ['404' => 'Jadwal tidak ditemukan.']);
    }

    $data['jadwal']  = $jadwal;
    $data['kelas']   = $this->kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();
    $data['mapel']   = $this->mapelModel->where('status', 'Aktif')->orderBy('nama_mapel', 'ASC')->findAll();
    $data['guru']    = $this->guruModel->orderBy('nama', 'ASC')->findAll();
    $data['ruangan'] = $this->ruanganModel->where('status', 'Aktif')->orderBy('nama_ruangan', 'ASC')->findAll();

    return view('admin/jadwal/edit', $data);
}

public function update($id)
{
    $jadwal = $this->jadwalModel->find($id);
    if (!$jadwal) {
        return redirect()->to('/admin/jadwal')->with('errors', ['404' => 'Jadwal tidak ditemukan.']);
    }

    $rules = [
        'id_kelas'    => 'required|is_natural_no_zero',
        'id_mapel'    => 'required|is_natural_no_zero',
        'id_guru'     => 'required|is_natural_no_zero',
        'id_ruangan'  => 'required|is_natural_no_zero',
        'hari'        => 'required|in_list[Senin,Selasa,Rabu,Kamis,Jumat,Sabtu]',
        'jam_mulai'   => 'required',
        'jam_selesai' => 'required',
    ];

    if (!$this->validate($rules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }

    $data = [
        'id_semester' => $jadwal['id_semester'],
        'id_kelas'    => $this->request->getPost('id_kelas'),
        'id_mapel'    => $this->request->getPost('id_mapel'),
        'id_guru'     => $this->request->getPost('id_guru'),
        'id_ruangan'  => $this->request->getPost('id_ruangan'),
        'hari'        => $this->request->getPost('hari'),
        'jam_mulai'   => $this->request->getPost('jam_mulai'),
        'jam_selesai' => $this->request->getPost('jam_selesai'),
    ];

    if (strtotime($data['jam_selesai']) <= strtotime($data['jam_mulai'])) {
        return redirect()->back()->withInput()->with('errors', ['jam' => 'Jam selesai harus lebih besar dari jam mulai.']);
    }

    $bentrok = $this->jadwalModel->cekBentrok($data, (int) $id);
    if (!empty($bentrok)) {
        return redirect()->back()->withInput()->with('errors', ['bentrok' => implode(' ', $bentrok)]);
    }

    $this->jadwalModel->update($id, $data);

    return redirect()->to('/admin/jadwal')->with('success', 'Jadwal berhasil diperbarui.');
}

public function delete($id)
{
    $jadwal = $this->jadwalModel->find($id);
    if (!$jadwal) {
        return redirect()->to('/admin/jadwal')->with('errors', ['404' => 'Jadwal tidak ditemukan.']);
    }

    $this->jadwalModel->delete($id);

    return redirect()->to('/admin/jadwal')->with('success', 'Jadwal berhasil dihapus.');
}

    // ---------- Import Excel (dengan Preview) ----------

    public function template()
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Template Jadwal');
    $sheet->fromArray(['Hari', 'Jam Mulai (HH:MM)', 'Jam Selesai (HH:MM)', 'Nama Kelas', 'Kode Mapel', 'NIP Guru', 'Nama Ruangan'], null, 'A1');
    $sheet->fromArray(['Senin', '07:00', '08:30', 'XI TJKT 1', 'indonesia', '45234564', 'R1'], null, 'A2');

    $ref = $spreadsheet->createSheet();
    $ref->setTitle('Referensi');
    $ref->fromArray(['Kode Mapel', 'Nama Mapel'], null, 'A1');
    $row = 2;
    foreach ($this->mapelModel->where('status', 'Aktif')->findAll() as $m) {
        $ref->setCellValue("A{$row}", $m['kode_mapel']); $ref->setCellValue("B{$row}", $m['nama_mapel']); $row++;
    }
    $ref->fromArray(['NIP Guru', 'Nama Guru'], null, 'D1');
    $row = 2;
    foreach ($this->guruModel->findAll() as $g) {
        $ref->setCellValue("D{$row}", $g['nip']); $ref->setCellValue("E{$row}", $g['nama']); $row++;
    }
    $ref->fromArray(['Nama Ruangan'], null, 'G1');
    $row = 2;
    foreach ($this->ruanganModel->where('status', 'Aktif')->findAll() as $r) {
        $ref->setCellValue("G{$row}", $r['nama_ruangan']); $row++;
    }

    $spreadsheet->setActiveSheetIndex(0);
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="template_jadwal.xlsx"');
    $writer->save('php://output');
    exit;
}
    public function importForm()
    {
        return view('admin/jadwal/import');
    }

    /**
     * Tahap 1: Upload -> parse -> validasi (tanpa insert) -> tampilkan Preview.
     * File disimpan sementara di writable/uploads/temp supaya bisa dipakai lagi di tahap konfirmasi,
     * tanpa perlu nyimpen ribuan baris data di session.
     */
    public function importPreview()
    {
        $file = $this->request->getFile('file_jadwal');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('errors', ['file' => 'File tidak valid atau belum dipilih.']);
        }

        $namaTemp = 'jadwal_preview_' . time() . '_' . $file->getRandomName();
        $file->move(WRITEPATH . 'uploads/temp', $namaTemp);

        $hasil = $this->parseDanValidasi(WRITEPATH . 'uploads/temp/' . $namaTemp);

        $data['baris']     = $hasil;
        $data['tempFile']  = $namaTemp;
        $data['jumlahOk']  = count(array_filter($hasil, fn($b) => $b['valid']));
        $data['jumlahGagal'] = count($hasil) - $data['jumlahOk'];

        return view('admin/jadwal/preview', $data);
    }

    /**
     * Tahap 2: Admin klik "Import Semua" -> baca ulang file temp -> insert baris yang valid saja.
     */
    public function importConfirm()
    {
        $namaTemp = $this->request->getPost('temp_file');
        $path     = WRITEPATH . 'uploads/temp/' . $namaTemp;

        if (!file_exists($path)) {
            return redirect()->to('/admin/jadwal/import')->with('errors', ['file' => 'Sesi upload sudah kadaluarsa, silakan upload ulang.']);
        }

        $hasil  = $this->parseDanValidasi($path);
        $sukses = 0;

        foreach ($hasil as $baris) {
            if (!$baris['valid']) continue;
            $this->jadwalModel->insert($baris['data']);
            $sukses++;
        }

        unlink($path); // hapus file temp setelah selesai

        return redirect()->to('/admin/jadwal')->with('success', "{$sukses} jadwal berhasil diimpor.");
    }

    /**
     * Fungsi inti: baca file Excel, cek tiap baris (kelas/mapel/guru/ruangan ada atau tidak, bentrok atau tidak),
     * dan return array status per baris — dipakai bareng di importPreview() & importConfirm() biar hasil konsisten.
     */
    private function parseDanValidasi(string $path): array
    {
        $semesterAktif = $this->semesterModel->getActive();
        $spreadsheet   = IOFactory::load($path);
        $rows          = $spreadsheet->getActiveSheet()->toArray();
        $hasil         = [];

        foreach ($rows as $i => $row) {
            if ($i === 0) continue; // header

            [$hari, $jamMulai, $jamSelesai, $namaKelas, $kodeMapel, $nip, $namaRuangan] = array_pad($row, 7, null);
            $baris = $i + 1;
            $hari = ucfirst(strtolower(trim((string)$hari)));

            if (empty($hari) && empty($namaKelas)) continue; // baris kosong, lewati diam-diam

            $errorBaris = [];

            if (!in_array($hari, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'], true)) {
                $errorBaris[] = "Hari '{$hari}' tidak valid";
            }
            if (strtotime($jamSelesai) <= strtotime($jamMulai)) {
                $errorBaris[] = "Jam selesai harus lebih besar dari jam mulai";
            }

            $kelas = $this->kelasModel->where('nama_kelas', trim((string)$namaKelas))->first();
            if (!$kelas) $errorBaris[] = "Kelas '{$namaKelas}' tidak ditemukan";

           $mapel = $this->mapelModel->where('kode_mapel', trim((string)$kodeMapel))->first();
            if (!$mapel) $errorBaris[] = "Kode mapel '{$kodeMapel}' tidak ditemukan";

            $guru = $this->guruModel->where('nip', trim((string)$nip))->first();
            if (!$guru) $errorBaris[] = "NIP guru '{$nip}' tidak ditemukan";

            $ruangan = $this->ruanganModel->where('nama_ruangan', trim((string)$namaRuangan))->first();
            if (!$ruangan) $errorBaris[] = "Ruangan '{$namaRuangan}' tidak ditemukan";

            $data = null;
            if (empty($errorBaris) && $semesterAktif) {
                $data = [
                    'id_semester' => $semesterAktif['id_semester'],
                    'id_kelas'    => $kelas['id_kelas'],
                    'id_mapel'    => $mapel['id_mapel'],
                    'id_guru'     => $guru['id_guru'],
                    'id_ruangan'  => $ruangan['id_ruangan'],
                    'hari'        => $hari,
                    'jam_mulai'   => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                ];

                $bentrok = $this->jadwalModel->cekBentrok($data);
                if (!empty($bentrok)) $errorBaris = array_merge($errorBaris, $bentrok);
            }

            $hasil[] = [
                'baris' => $baris,
                'valid' => empty($errorBaris),
                'error' => implode('; ', $errorBaris),
                'raw'   => compact('hari', 'jamMulai', 'jamSelesai', 'namaKelas', 'kodeMapel', 'nip', 'namaRuangan'),
                'data'  => $data,
            ];
        }

        return $hasil;
    }

    public function exportExcel()
{
    $semesterAktif = $this->semesterModel->getActive();
    $jadwal = $this->jadwalModel->getAllWithRelasi($semesterAktif['id_semester']);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Jadwal Pelajaran');
    $sheet->fromArray(['Hari', 'Jam Mulai', 'Jam Selesai', 'Kelas', 'Jurusan', 'Mapel', 'Guru', 'Ruangan', 'Status'], null, 'A1');

    $row = 2;
    foreach ($jadwal as $j) {
        $sheet->fromArray([
            $j['hari'], substr($j['jam_mulai'], 0, 5), substr($j['jam_selesai'], 0, 5),
            $j['nama_kelas'], $j['nama_jurusan'] ?? '-', $j['nama_mapel'], $j['nama_guru'], $j['nama_ruangan'], $j['status'],
        ], null, "A{$row}");
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="jadwal_' . date('Y-m-d') . '.xlsx"');
    $writer->save('php://output');
    exit;
}
}