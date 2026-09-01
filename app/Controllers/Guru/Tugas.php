<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\JadwalModel;
use App\Models\TugasModel;
use App\Models\TugasSubmisiModel;
use App\Models\PengaturanNilaiModel;
use App\Models\KomponenNilaiModel;
use App\Models\NilaiSiswaModel;
use App\Models\SemesterModel;
use App\Models\KelasSiswaModel;
use App\Models\TahunAjaranModel;

class Tugas extends BaseController
{
    protected function getGuruLogin()
    {
        $userId    = session()->get('id_user');
        $guruModel = new GuruModel();
        $guru      = $guruModel->where('user_id', $userId)->first();

        if (!$guru) {
            throw new \RuntimeException('DATA_GURU_TIDAK_DITEMUKAN');
        }

        return $guru;
    }

    protected function redirectGuruTidakDitemukan()
    {
        return redirect()->to('/logout')
            ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
    }

    protected function getJadwalMilikSaya($idJadwal, $guru)
    {
        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return null;
        }

        return $jadwal;
    }

    public function index()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $tugasModel = new TugasModel();
        $daftar = $tugasModel->getByGuru($guru['id_guru']);

        // Hitung jumlah submisi per tugas (biar guru lihat progress tanpa buka satu-satu)
        $submisiModel = new TugasSubmisiModel();
        foreach ($daftar as &$t) {
            $t['jumlah_submisi'] = $submisiModel->where('id_tugas', $t['id_tugas'])->countAllResults();
        }
        unset($t);

        return view('guru/tugas/index', [
            'guru'   => $guru,
            'daftar' => $daftar,
        ]);
    }

    public function create($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $jadwal = $this->getJadwalMilikSaya($idJadwal, $guru);
        if (!$jadwal) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $komponenList = $this->getKomponenUntukJadwal($guru, $jadwal);

        return view('guru/tugas/form', [
            'jadwal'       => $jadwal,
            'tugas'        => null,
            'komponenList' => $komponenList,
        ]);
    }

    protected function getKomponenUntukJadwal($guru, $jadwal): array
    {
        $semesterModel = new SemesterModel();
        $semesterAktif = $semesterModel->getActive();
        if (!$semesterAktif) {
            return [];
        }

        $pengaturanModel = new PengaturanNilaiModel();
        $pengaturan = $pengaturanModel->getPengaturan(
            (int) $guru['id_guru'],
            (int) $jadwal['id_kelas'],
            (int) $jadwal['id_mapel'],
            (int) $semesterAktif['id_semester']
        );

        if (!$pengaturan) {
            return [];
        }

        $komponenModel = new KomponenNilaiModel();
        return $komponenModel->where('id_pengaturan', $pengaturan['id_pengaturan'])
            ->orderBy('urutan', 'ASC')->findAll();
    }

    public function store()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $idJadwal = $this->request->getPost('id_jadwal');
        $jadwal = $this->getJadwalMilikSaya($idJadwal, $guru);
        if (!$jadwal) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        // Validasi upload: extension whitelist + mime + size
        $fileLampiran = $this->request->getFile('file_lampiran');
        if ($fileLampiran && $fileLampiran->isValid()) {
            $rules = [
                'file_lampiran' => 'max_size[file_lampiran,5120]|ext_in[file_lampiran,pdf,doc,docx,ppt,pptx,jpg,jpeg,png,zip]|mime_in[file_lampiran,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,application/zip,application/x-zip-compressed]',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $tugasModel = new TugasModel();

        $data = [
            'id_jadwal'     => $idJadwal,
            'id_guru'       => $guru['id_guru'],
            'id_komponen'   => $this->request->getPost('id_komponen') ?: null,
            'judul'         => $this->request->getPost('judul'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tenggat'       => $this->request->getPost('tenggat'),
            'status'        => 'Aktif',
        ];

        if ($fileLampiran && $fileLampiran->isValid() && !$fileLampiran->hasMoved()) {
            $namaFile = 'tugas_' . time() . '_' . $fileLampiran->getRandomName();
            $fileLampiran->move(WRITEPATH . 'uploads/tugas', $namaFile);
            $data['file_lampiran'] = $namaFile;
        }

        if (!$tugasModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $tugasModel->errors());
        }

        return redirect()->to('/guru/tugas')->with('success', 'Tugas berhasil dibuat.');
    }

    public function edit($idTugas)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $tugasModel = new TugasModel();
        $tugas = $tugasModel->find($idTugas);

        if (!$tugas || $tugas['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/tugas')->with('errors', ['403' => 'Tugas ini bukan milik Anda.']);
        }

        $jadwal = $this->getJadwalMilikSaya($tugas['id_jadwal'], $guru);
        $komponenList = $this->getKomponenUntukJadwal($guru, $jadwal);

        return view('guru/tugas/form', [
            'jadwal'       => $jadwal,
            'tugas'        => $tugas,
            'komponenList' => $komponenList,
        ]);
    }

    public function update($idTugas)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $tugasModel = new TugasModel();
        $tugas = $tugasModel->find($idTugas);

        if (!$tugas || $tugas['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/tugas')->with('errors', ['403' => 'Tugas ini bukan milik Anda.']);
        }

        // Validasi upload: extension whitelist + mime + size
        $fileLampiran = $this->request->getFile('file_lampiran');
        if ($fileLampiran && $fileLampiran->isValid()) {
            $rules = [
                'file_lampiran' => 'max_size[file_lampiran,5120]|ext_in[file_lampiran,pdf,doc,docx,ppt,pptx,jpg,jpeg,png,zip]|mime_in[file_lampiran,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,image/jpeg,image/png,application/zip,application/x-zip-compressed]',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $data = [
            'id_komponen'   => $this->request->getPost('id_komponen') ?: null,
            'judul'         => $this->request->getPost('judul'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'tanggal_mulai' => $this->request->getPost('tanggal_mulai'),
            'tenggat'       => $this->request->getPost('tenggat'),
            'status'        => $this->request->getPost('status'),
        ];

        if ($fileLampiran && $fileLampiran->isValid() && !$fileLampiran->hasMoved()) {
            $namaFile = 'tugas_' . time() . '_' . $fileLampiran->getRandomName();
            $fileLampiran->move(WRITEPATH . 'uploads/tugas', $namaFile);

            if (!empty($tugas['file_lampiran']) && file_exists(WRITEPATH . 'uploads/tugas/' . $tugas['file_lampiran'])) {
                @unlink(WRITEPATH . 'uploads/tugas/' . $tugas['file_lampiran']);
            }

            $data['file_lampiran'] = $namaFile;
        }

        if (!$tugasModel->update($idTugas, $data)) {
            return redirect()->back()->withInput()->with('errors', $tugasModel->errors());
        }

        return redirect()->to('/guru/tugas')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function delete($idTugas)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $tugasModel = new TugasModel();
        $tugas = $tugasModel->find($idTugas);

        if (!$tugas || $tugas['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/tugas')->with('errors', ['403' => 'Tugas ini bukan milik Anda.']);
        }

        if (!empty($tugas['file_lampiran']) && file_exists(WRITEPATH . 'uploads/tugas/' . $tugas['file_lampiran'])) {
            @unlink(WRITEPATH . 'uploads/tugas/' . $tugas['file_lampiran']);
        }

        $tugasModel->delete($idTugas); // submisi ikut terhapus (ON DELETE CASCADE)

        return redirect()->to('/guru/tugas')->with('success', 'Tugas berhasil dihapus.');
    }

    public function submisi($idTugas)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $tugasModel = new TugasModel();
        $tugas = $tugasModel->select('tugas.*, kelas.nama_kelas, mapel.nama_mapel, jadwal.id_kelas')
            ->join('jadwal', 'jadwal.id_jadwal = tugas.id_jadwal')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idTugas);

        if (!$tugas || $tugas['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/tugas')->with('errors', ['403' => 'Tugas ini bukan milik Anda.']);
        }

        $submisiModel = new TugasSubmisiModel();
        $daftarSubmisi = $submisiModel->getByTugas($idTugas);

        // Siswa yang belum submit (dari kelas ybs, tahun ajaran aktif)
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        $siswaBelumSubmit = [];
        if ($tahunAktif) {
            $kelasSiswaModel = new KelasSiswaModel();
            $siswaSekelas = $kelasSiswaModel->getSiswaByKelas($tugas['id_kelas'], $tahunAktif['id_tahun_ajaran']);

            $idSudahSubmit = array_column($daftarSubmisi, 'id_siswa');
            foreach ($siswaSekelas as $s) {
                if (!in_array($s['id_siswa'], $idSudahSubmit)) {
                    $siswaBelumSubmit[] = $s;
                }
            }
        }

        return view('guru/tugas/submisi', [
            'tugas'            => $tugas,
            'daftarSubmisi'    => $daftarSubmisi,
            'siswaBelumSubmit' => $siswaBelumSubmit,
        ]);
    }

    public function simpanNilai($idSubmisi)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $submisiModel = new TugasSubmisiModel();
        $submisi = $submisiModel->find($idSubmisi);

        if (!$submisi) {
            return redirect()->back()->with('errors', ['404' => 'Submisi tidak ditemukan.']);
        }

        $tugasModel = new TugasModel();
        $tugas = $tugasModel->find($submisi['id_tugas']);

        if (!$tugas || $tugas['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/tugas')->with('errors', ['403' => 'Tugas ini bukan milik Anda.']);
        }

        $nilaiRaw = str_replace(',', '.', trim((string) $this->request->getPost('nilai')));
        if ($nilaiRaw === '' || !is_numeric($nilaiRaw)) {
            return redirect()->back()->with('errors', ['nilai' => 'Nilai harus berupa angka.']);
        }
        $nilai = (float) $nilaiRaw;

        $db = \Config\Database::connect();
        $db->transStart();

        $submisiModel->update($idSubmisi, [
            'nilai'        => $nilai,
            'status'       => 'Dinilai',
            'catatan_guru' => $this->request->getPost('catatan_guru'),
        ]);

        // Sinkronisasi ke sistem Nilai resmi, HANYA kalau tugas ini terhubung ke sebuah komponen nilai
        if (!empty($tugas['id_komponen'])) {
            $nilaiSiswaModel = new NilaiSiswaModel();
            $existing = $nilaiSiswaModel->where('id_siswa', $submisi['id_siswa'])
                ->where('id_komponen', $tugas['id_komponen'])->first();

            if ($existing) {
                $nilaiSiswaModel->update($existing['id_nilai'], [
                    'nilai'      => $nilai,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $nilaiSiswaModel->insert([
                    'id_siswa'    => $submisi['id_siswa'],
                    'id_komponen' => $tugas['id_komponen'],
                    'nilai'       => $nilai,
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, nilai tidak tersimpan.']);
        }

        $pesan = 'Nilai berhasil disimpan.';
        if (!empty($tugas['id_komponen'])) {
            $pesan .= ' Nilai ini juga otomatis masuk ke komponen nilai terkait.';
        }

        return redirect()->to('/guru/tugas/submisi/' . $tugas['id_tugas'])->with('success', $pesan);
    }

        /**
     * Unduh file lampiran tugas.
     * Dibuka untuk guru pemilik tugas ATAU siswa yang berada di kelas tugas tsb.
     */
    public function unduhLampiran($idTugas)
    {
        $userId = session()->get('id_user');
        $role   = session()->get('role');

        $tugasModel = new TugasModel();
        $tugas = $tugasModel->select('tugas.*, jadwal.id_kelas')
            ->join('jadwal', 'jadwal.id_jadwal = tugas.id_jadwal')
            ->find($idTugas);

        if (!$tugas || empty($tugas['file_lampiran'])) {
            return redirect()->back()->with('errors', ['404' => 'File tidak ditemukan.']);
        }

        $boleh = false;

        if ($role === 'Guru') {
            $guruModel = new GuruModel();
            $guru = $guruModel->where('user_id', $userId)->first();
            $boleh = $guru && (int) $tugas['id_guru'] === (int) $guru['id_guru'];
        } elseif ($role === 'Siswa') {
            $siswaModel = new \App\Models\SiswaModel();
            $siswa = $siswaModel->where('user_id', $userId)->first();

            if ($siswa) {
                $tahunAjaranModel = new TahunAjaranModel();
                $tahunAktif = $tahunAjaranModel->getActive();

                if ($tahunAktif) {
                    $kelasSiswaModel = new KelasSiswaModel();
                    $relasi = $kelasSiswaModel
                        ->where('id_siswa', $siswa['id_siswa'])
                        ->where('id_kelas', $tugas['id_kelas'])
                        ->where('id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
                        ->first();
                    $boleh = (bool) $relasi;
                }
            }
        }

        if (!$boleh) {
            return redirect()->back()->with('errors', ['403' => 'Anda tidak memiliki akses ke file ini.']);
        }

        $path = WRITEPATH . 'uploads/tugas/' . $tugas['file_lampiran'];
        if (!file_exists($path)) {
            return redirect()->back()->with('errors', ['404' => 'File tidak ditemukan di server.']);
        }

        return $this->response->download($path, null);
    }
}