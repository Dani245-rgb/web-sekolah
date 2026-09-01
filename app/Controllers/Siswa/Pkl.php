<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\PklPenempatanModel;
use App\Models\PklJurnalModel;

class Pkl extends BaseController
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

    public function index()
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data siswa Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $pklModel = new PklPenempatanModel();
        $riwayatPenempatan = $pklModel->getBySiswa($siswa['id_siswa']);
        $penempatanAktif   = $pklModel->getAktifBySiswa($siswa['id_siswa']);

        $daftarJurnal = [];
        if ($penempatanAktif) {
            $jurnalModel  = new PklJurnalModel();
            $daftarJurnal = $jurnalModel->getByPenempatan($penempatanAktif['id_penempatan']);
        }

        return view('siswa/pkl/index', [
            'siswa'              => $siswa,
            'penempatanAktif'    => $penempatanAktif,
            'riwayatPenempatan'  => $riwayatPenempatan,
            'daftarJurnal'       => $daftarJurnal,
        ]);
    }

    public function simpanJurnal()
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data siswa Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $pklModel = new PklPenempatanModel();
        $penempatanAktif = $pklModel->getAktifBySiswa($siswa['id_siswa']);

        if (!$penempatanAktif) {
            return redirect()->to('/siswa/pkl')->with('errors', ['404' => 'Anda tidak memiliki penempatan PKL yang aktif.']);
        }

        $tanggal  = $this->request->getPost('tanggal');
        $kegiatan = trim((string) $this->request->getPost('kegiatan'));
        $kendala  = trim((string) $this->request->getPost('kendala'));

        if ($tanggal === '' || $kegiatan === '') {
            return redirect()->back()->withInput()->with('errors', ['kosong' => 'Tanggal dan kegiatan wajib diisi.']);
        }

        // Cegah tanggal di luar rentang periode PKL
        if ($tanggal < $penempatanAktif['tanggal_mulai'] || ($penempatanAktif['tanggal_selesai'] && $tanggal > $penempatanAktif['tanggal_selesai'])) {
            return redirect()->back()->withInput()->with('errors', ['tanggal' => 'Tanggal di luar periode PKL Anda.']);
        }

        $jurnalModel = new PklJurnalModel();

        if ($jurnalModel->sudahAdaJurnalHariItu($penempatanAktif['id_penempatan'], $tanggal)) {
            return redirect()->back()->withInput()->with('errors', ['duplikat' => 'Jurnal untuk tanggal ini sudah pernah diisi.']);
        }

        $jurnalModel->insert([
            'id_penempatan'   => $penempatanAktif['id_penempatan'],
            'tanggal'         => $tanggal,
            'kegiatan'        => $kegiatan,
            'kendala'         => $kendala ?: null,
            'status_validasi' => 'Menunggu',
        ]);

        return redirect()->to('/siswa/pkl')->with('success', 'Jurnal PKL berhasil disimpan, menunggu validasi pembimbing.');
    }
}