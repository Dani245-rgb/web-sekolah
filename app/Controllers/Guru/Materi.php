<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\JadwalModel;
use App\Models\MateriModel;

class Materi extends BaseController
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

        $materiModel = new MateriModel();
        $daftar = $materiModel->getByGuru($guru['id_guru']);

        return view('guru/materi/index', [
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

        return view('guru/materi/form', [
            'jadwal' => $jadwal,
            'materi' => null,
        ]);
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

        $fileMateri = $this->request->getFile('file_materi');
        if (!$fileMateri || !$fileMateri->isValid() || $fileMateri->hasMoved()) {
            return redirect()->back()->withInput()->with('errors', ['file' => 'File materi wajib diunggah.']);
        }

        $rules = [
            'file_materi' => 'max_size[file_materi,10240]|ext_in[file_materi,pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip]|mime_in[file_materi,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,application/zip,application/x-zip-compressed]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaFile = 'materi_' . time() . '_' . $fileMateri->getRandomName();
        $fileMateri->move(WRITEPATH . 'uploads/materi', $namaFile);

        $materiModel = new MateriModel();
        $data = [
            'id_jadwal'   => $idJadwal,
            'id_guru'     => $guru['id_guru'],
            'judul'       => $this->request->getPost('judul'),
            'deskripsi'   => $this->request->getPost('deskripsi'),
            'file_materi' => $namaFile,
        ];

        if (!$materiModel->save($data)) {
            @unlink(FCPATH . 'assets/uploads/materi/' . $namaFile);
            return redirect()->back()->withInput()->with('errors', $materiModel->errors());
        }

        return redirect()->to('/guru/materi')->with('success', 'Materi berhasil diunggah.');
    }

    public function edit($idMateri)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $materiModel = new MateriModel();
        $materi = $materiModel->find($idMateri);

        if (!$materi || $materi['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/materi')->with('errors', ['403' => 'Materi ini bukan milik Anda.']);
        }

        $jadwal = $this->getJadwalMilikSaya($materi['id_jadwal'], $guru);

        return view('guru/materi/form', [
            'jadwal' => $jadwal,
            'materi' => $materi,
        ]);
    }

    public function update($idMateri)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $materiModel = new MateriModel();
        $materi = $materiModel->find($idMateri);

        if (!$materi || $materi['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/materi')->with('errors', ['403' => 'Materi ini bukan milik Anda.']);
        }

        $data = [
            'judul'     => $this->request->getPost('judul'),
            'deskripsi' => $this->request->getPost('deskripsi'),
        ];

        $fileMateri = $this->request->getFile('file_materi');
        if ($fileMateri && $fileMateri->isValid()) {
            $rules = [
                'file_materi' => 'max_size[file_materi,10240]|ext_in[file_materi,pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,zip]|mime_in[file_materi,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,application/zip,application/x-zip-compressed]',
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        if ($fileMateri && $fileMateri->isValid() && !$fileMateri->hasMoved()) {
            $namaFile = 'materi_' . time() . '_' . $fileMateri->getRandomName();
            $fileMateri->move(WRITEPATH . 'uploads/materi', $namaFile);

            if (!empty($materi['file_materi']) && file_exists(WRITEPATH . 'uploads/materi/' . $materi['file_materi'])) {
                @unlink(WRITEPATH . 'uploads/materi/' . $materi['file_materi']);
            }

            $data['file_materi'] = $namaFile;
        }

        if (!$materiModel->update($idMateri, $data)) {
            return redirect()->back()->withInput()->with('errors', $materiModel->errors());
        }

        return redirect()->to('/guru/materi')->with('success', 'Materi berhasil diperbarui.');
    }

    public function delete($idMateri)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $materiModel = new MateriModel();
        $materi = $materiModel->find($idMateri);

        if (!$materi || $materi['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/materi')->with('errors', ['403' => 'Materi ini bukan milik Anda.']);
        }

        if (!empty($materi['file_materi']) && file_exists(WRITEPATH . 'uploads/materi/' . $materi['file_materi'])) {
            @unlink(WRITEPATH . 'uploads/materi/' . $materi['file_materi']);
        }

        $materiModel->delete($idMateri);

        return redirect()->to('/guru/materi')->with('success', 'Materi berhasil dihapus.');
    }

        public function unduhFile($idMateri)
    {
        $userId = session()->get('id_user');
        $role   = session()->get('role');

        $materiModel = new MateriModel();
        $materi = $materiModel->select('materi.*, jadwal.id_kelas')
            ->join('jadwal', 'jadwal.id_jadwal = materi.id_jadwal')
            ->find($idMateri);

        if (!$materi || empty($materi['file_materi'])) {
            return redirect()->back()->with('errors', ['404' => 'File tidak ditemukan.']);
        }

        $boleh = false;

        if ($role === 'Guru') {
            $guruModel = new GuruModel();
            $guru = $guruModel->where('user_id', $userId)->first();
            $boleh = $guru && (int) $materi['id_guru'] === (int) $guru['id_guru'];
        } elseif ($role === 'Siswa') {
            $siswaModel = new \App\Models\SiswaModel();
            $siswa = $siswaModel->where('user_id', $userId)->first();

            if ($siswa) {
                $tahunAjaranModel = new \App\Models\TahunAjaranModel();
                $tahunAktif = $tahunAjaranModel->getActive();

                if ($tahunAktif) {
                    $kelasSiswaModel = new \App\Models\KelasSiswaModel();
                    $relasi = $kelasSiswaModel
                        ->where('id_siswa', $siswa['id_siswa'])
                        ->where('id_kelas', $materi['id_kelas'])
                        ->where('id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
                        ->first();
                    $boleh = (bool) $relasi;
                }
            }
        }

        if (!$boleh) {
            return redirect()->back()->with('errors', ['403' => 'Anda tidak memiliki akses ke file ini.']);
        }

        $path = WRITEPATH . 'uploads/materi/' . $materi['file_materi'];
        if (!file_exists($path)) {
            return redirect()->back()->with('errors', ['404' => 'File tidak ditemukan di server.']);
        }

        return $this->response->download($path, null);
    }
}