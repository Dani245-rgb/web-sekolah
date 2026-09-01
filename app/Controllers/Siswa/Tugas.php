<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasSiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\JadwalModel;
use App\Models\TugasModel;
use App\Models\TugasSubmisiModel;
use App\Models\GuruModel;

class Tugas extends BaseController
{
    protected function getSiswaLogin()
    {
        $userId     = session()->get('id_user');
        $siswaModel = new SiswaModel();
        $siswa      = $siswaModel->where('user_id', $userId)->first();

        if (!$siswa) {
            throw new \RuntimeException('DATA_SISWA_TIDAK_DITEMUKAN');
        }

        return $siswa;
    }

    protected function redirectSiswaTidakDitemukan()
    {
        return redirect()->to('/logout')
            ->with('errors', ['akun' => 'Data siswa Anda tidak ditemukan. Silakan hubungi Admin.']);
    }

    protected function getKelasAktif($siswa)
    {
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();
        if (!$tahunAktif) {
            return null;
        }

        $kelasSiswaModel = new KelasSiswaModel();
        $relasi = $kelasSiswaModel
            ->where('id_siswa', $siswa['id_siswa'])
            ->where('id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
            ->first();

        return $relasi ? $relasi['id_kelas'] : null;
    }

    public function index()
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectSiswaTidakDitemukan();
        }

        $idKelas = $this->getKelasAktif($siswa);
        if (!$idKelas) {
            return view('siswa/tugas/index', [
                'daftar' => [],
                'errors' => 'Anda belum terdaftar di kelas aktif manapun.',
            ]);
        }

        $tugasModel = new TugasModel();
        $daftar = $tugasModel->select('tugas.*, mapel.nama_mapel, kelas.nama_kelas')
            ->join('jadwal', 'jadwal.id_jadwal = tugas.id_jadwal')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->where('jadwal.id_kelas', $idKelas)
            ->where('tugas.status', 'Aktif')
            ->orderBy('tugas.tenggat', 'ASC')
            ->findAll();

        // Tandai status submisi milik siswa ini untuk tiap tugas
        $submisiModel = new TugasSubmisiModel();
        foreach ($daftar as &$t) {
            $submisi = $submisiModel
                ->where('id_tugas', $t['id_tugas'])
                ->where('id_siswa', $siswa['id_siswa'])
                ->first();

            $t['submisi_status'] = $submisi ? $submisi['status'] : 'Belum Kumpul';
            $t['submisi_nilai']  = $submisi['nilai'] ?? null;

            // Tandai terlambat kalau belum kumpul & sudah lewat tenggat
            if (!$submisi && strtotime($t['tenggat']) < time()) {
                $t['submisi_status'] = 'Lewat Tenggat';
            }
        }
        unset($t);

        return view('siswa/tugas/index', [
            'daftar' => $daftar,
        ]);
    }

    public function detail($idTugas)
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectSiswaTidakDitemukan();
        }

        $idKelas = $this->getKelasAktif($siswa);

        $tugasModel = new TugasModel();
        $tugas = $tugasModel->select('tugas.*, mapel.nama_mapel, kelas.nama_kelas, jadwal.id_kelas')
            ->join('jadwal', 'jadwal.id_jadwal = tugas.id_jadwal')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->find($idTugas);

        if (!$tugas || $tugas['id_kelas'] != $idKelas) {
            return redirect()->to('/siswa/tugas')->with('errors', ['403' => 'Tugas ini bukan untuk kelas Anda.']);
        }

        $submisiModel = new TugasSubmisiModel();
        $submisi = $submisiModel
            ->where('id_tugas', $idTugas)
            ->where('id_siswa', $siswa['id_siswa'])
            ->first();

        $sudahLewatTenggat = strtotime($tugas['tenggat']) < time();

        return view('siswa/tugas/detail', [
            'tugas'              => $tugas,
            'submisi'            => $submisi,
            'sudahLewatTenggat'  => $sudahLewatTenggat,
        ]);
    }

    public function kumpulkan($idTugas)
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectSiswaTidakDitemukan();
        }

        $idKelas = $this->getKelasAktif($siswa);

        $tugasModel = new TugasModel();
        $tugas = $tugasModel->select('tugas.*, jadwal.id_kelas')
            ->join('jadwal', 'jadwal.id_jadwal = tugas.id_jadwal')
            ->find($idTugas);

        if (!$tugas || $tugas['id_kelas'] != $idKelas) {
            return redirect()->to('/siswa/tugas')->with('errors', ['403' => 'Tugas ini bukan untuk kelas Anda.']);
        }

        if ($tugas['status'] !== 'Aktif') {
            return redirect()->to('/siswa/tugas/detail/' . $idTugas)
                ->with('errors', ['ditutup' => 'Tugas ini sudah ditutup, tidak bisa dikumpulkan lagi.']);
        }

        $isiJawaban = $this->request->getPost('isi_jawaban');
        $fileJawaban = $this->request->getFile('file_jawaban');

        // Validasi upload: extension whitelist + mime + size, cegah upload file berbahaya (.php, .exe, dll)
        if ($fileJawaban && $fileJawaban->isValid()) {
            $rules = [
                'file_jawaban' => 'max_size[file_jawaban,5120]|ext_in[file_jawaban,pdf,doc,docx,jpg,jpeg,png,zip]|mime_in[file_jawaban,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,application/zip,application/x-zip-compressed]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $adaFileBaru = $fileJawaban && $fileJawaban->isValid() && !$fileJawaban->hasMoved();

        if (empty($isiJawaban) && !$adaFileBaru) {
            return redirect()->back()->with('errors', ['kosong' => 'Isi jawaban atau lampirkan file terlebih dahulu.']);
        }

        $submisiModel = new TugasSubmisiModel();
        $existing = $submisiModel
            ->where('id_tugas', $idTugas)
            ->where('id_siswa', $siswa['id_siswa'])
            ->first();

        // Kalau sudah pernah dinilai, siswa tidak boleh ubah jawaban lagi (biar nilai konsisten)
        if ($existing && $existing['status'] === 'Dinilai') {
            return redirect()->back()->with('errors', ['dinilai' => 'Tugas ini sudah dinilai, jawaban tidak bisa diubah lagi.']);
        }

        $statusSubmisi = strtotime($tugas['tenggat']) < time() ? 'Terlambat' : 'Menunggu';

        $data = [
            'id_tugas'     => $idTugas,
            'id_siswa'     => $siswa['id_siswa'],
            'isi_jawaban'  => $isiJawaban,
            'status'       => $statusSubmisi,
            'waktu_kumpul' => date('Y-m-d H:i:s'),
        ];

        if ($adaFileBaru) {
            $namaFile = 'jawaban_' . time() . '_' . $fileJawaban->getRandomName();
            $fileJawaban->move(WRITEPATH . 'uploads/jawaban', $namaFile);

            if ($existing && !empty($existing['file_jawaban']) && file_exists(WRITEPATH . 'uploads/jawaban/' . $existing['file_jawaban'])) {
                @unlink(WRITEPATH . 'uploads/jawaban/' . $existing['file_jawaban']);
            }

            $data['file_jawaban'] = $namaFile;
        } elseif ($existing) {
            // Tidak upload file baru → pertahankan file lama (kalau ada)
            $data['file_jawaban'] = $existing['file_jawaban'];
        }

        if ($existing) {
            $submisiModel->update($existing['id_submisi'], $data);
            $pesan = 'Jawaban berhasil diperbarui.';
        } else {
            $submisiModel->insert($data);
            $pesan = 'Tugas berhasil dikumpulkan.';
        }

        return redirect()->to('/siswa/tugas/detail/' . $idTugas)->with('success', $pesan);
    }

    public function unduhJawaban($idSubmisi)
    {
        $userId = session()->get('id_user');
        $role   = session()->get('role');

        $submisiModel = new TugasSubmisiModel();
        $submisi = $submisiModel->find($idSubmisi);

        if (!$submisi || empty($submisi['file_jawaban'])) {
            return redirect()->back()->with('errors', ['404' => 'File tidak ditemukan.']);
        }

        $boleh = false;

        if ($role === 'Siswa') {
            // Hanya pemilik jawaban yang boleh unduh
            $siswa = $this->getSiswaLoginAtauNull();
            $boleh = $siswa && (int) $submisi['id_siswa'] === (int) $siswa['id_siswa'];
        } elseif ($role === 'Guru') {
            // Guru pengampu tugas terkait boleh unduh untuk keperluan penilaian
            $tugasModel = new TugasModel();
            $tugas = $tugasModel->find($submisi['id_tugas']);

            $guruModel = new GuruModel();
            $guru = $guruModel->where('user_id', $userId)->first();

            $boleh = $tugas && $guru && (int) $tugas['id_guru'] === (int) $guru['id_guru'];
        }

        if (!$boleh) {
            return redirect()->back()->with('errors', ['403' => 'Anda tidak memiliki akses ke file ini.']);
        }

        $path = WRITEPATH . 'uploads/jawaban/' . $submisi['file_jawaban'];
        if (!file_exists($path)) {
            return redirect()->back()->with('errors', ['404' => 'File tidak ditemukan di server.']);
        }

        return $this->response->download($path, null);
    }

    /**
     * Sama seperti getSiswaLogin(), tapi return null bukan throw exception.
     * Dipakai di unduhJawaban() karena endpoint ini diakses lintas role,
     * jadi "bukan siswa" itu kondisi normal (guru), bukan error.
     */
    protected function getSiswaLoginAtauNull()
    {
        try {
            return $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return null;
        }
    }
}
