<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\PklPenempatanModel;
use App\Models\PklJurnalModel;

class Pkl extends BaseController
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

    public function index()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $pklModel = new PklPenempatanModel();
        $daftar = $pklModel->getAllWithRelasi(['id_guru_pembimbing' => $guru['id_guru']]);

        return view('guru/pkl/index', [
            'guru'   => $guru,
            'daftar' => $daftar,
        ]);
    }

    public function jurnal($idPenempatan)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $pklModel = new PklPenempatanModel();
        $penempatan = $pklModel->select('pkl_penempatan.*, siswa.nama as nama_siswa, siswa.nis')
            ->join('siswa', 'siswa.id_siswa = pkl_penempatan.id_siswa')
            ->find($idPenempatan);

        if (!$penempatan || $penempatan['id_guru_pembimbing'] != $guru['id_guru']) {
            return redirect()->to('/guru/pkl')->with('errors', ['403' => 'Data ini bukan siswa bimbingan Anda.']);
        }

        $jurnalModel = new PklJurnalModel();
        $daftarJurnal = $jurnalModel->getByPenempatan($idPenempatan);

        return view('guru/pkl/jurnal', [
            'penempatan'   => $penempatan,
            'daftarJurnal' => $daftarJurnal,
        ]);
    }

    public function validasiJurnal($idJurnal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $jurnalModel = new PklJurnalModel();
        $pklModel    = new PklPenempatanModel();

        $jurnal = $jurnalModel->find($idJurnal);
        if (!$jurnal) {
            return redirect()->back()->with('errors', ['404' => 'Jurnal tidak ditemukan.']);
        }

        $penempatan = $pklModel->find($jurnal['id_penempatan']);
        if (!$penempatan || $penempatan['id_guru_pembimbing'] != $guru['id_guru']) {
            return redirect()->to('/guru/pkl')->with('errors', ['403' => 'Jurnal ini bukan milik siswa bimbingan Anda.']);
        }

        $status = $this->request->getPost('status_validasi');
        if (!in_array($status, ['Disetujui', 'Ditolak'], true)) {
            return redirect()->back()->with('errors', ['status' => 'Status validasi tidak valid.']);
        }

        $jurnalModel->update($idJurnal, [
            'status_validasi'    => $status,
            'catatan_pembimbing' => $this->request->getPost('catatan_pembimbing'),
        ]);

        return redirect()->to('/guru/pkl/jurnal/' . $jurnal['id_penempatan'])
            ->with('success', 'Jurnal berhasil ' . strtolower($status) . '.');
    }
}