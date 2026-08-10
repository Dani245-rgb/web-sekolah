<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\JadwalModel;
use App\Models\KelasSiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\AbsensiJadwalModel;
use App\Models\AbsensiDetailModel;

class Absensi extends BaseController
{
    protected function getGuruLogin()
    {
        $userId = session()->get('id_user');
        $guruModel = new GuruModel();
        return $guruModel->where('user_id', $userId)->first();
    }

    public function form($idJadwal)
    {
        $guru = $this->getGuruLogin();
        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        $kelasSiswaModel = new KelasSiswaModel();
        $siswaList = $tahunAktif
            ? $kelasSiswaModel->getSiswaByKelas($jadwal['id_kelas'], $tahunAktif['id_tahun_ajaran'])
            : [];

        $absensiJadwalModel = new AbsensiJadwalModel();
        $absensiDetailModel = new AbsensiDetailModel();
        $existing = $absensiJadwalModel->cariByJadwalTanggal($idJadwal, $tanggal);

        $statusTersimpan = [];
        if ($existing) {
            $detail = $absensiDetailModel->where('id_absensi_jadwal', $existing['id_absensi_jadwal'])->findAll();
            foreach ($detail as $d) {
                $statusTersimpan[$d['id_siswa']] = ['status' => $d['status'], 'keterangan' => $d['keterangan']];
            }
        }

        $data['jadwal']          = $jadwal;
        $data['tanggal']         = $tanggal;
        $data['siswaList']       = $siswaList;
        $data['statusTersimpan'] = $statusTersimpan;
        $data['mode']            = $existing ? 'edit' : 'baru';

        return view('guru/absensi/form', $data);
    }

    public function simpan()
    {
        $guru = $this->getGuruLogin();
        $idJadwal = $this->request->getPost('id_jadwal');
        $tanggal  = $this->request->getPost('tanggal');
        $statusArr = $this->request->getPost('status');
        $keteranganArr = $this->request->getPost('keterangan');

        // Pengaman: kalau gak ada siswa yang dikirim (kelas kosong / form rusak)
        if (empty($statusArr) || !is_array($statusArr)) {
            return redirect()->back()->with('errors', ['kosong' => 'Tidak ada data siswa untuk disimpan. Pastikan kelas ini sudah punya siswa yang di-assign.']);
        }

        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->find($idJadwal);
        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $absensiJadwalModel = new AbsensiJadwalModel();
        $absensiDetailModel = new AbsensiDetailModel();

        $db = \Config\Database::connect();
        $db->transStart();

        $existing = $absensiJadwalModel->cariByJadwalTanggal($idJadwal, $tanggal);
        if ($existing) {
            $idAbsensiJadwal = $existing['id_absensi_jadwal'];
            $absensiDetailModel->where('id_absensi_jadwal', $idAbsensiJadwal)->delete();
        } else {
            $idAbsensiJadwal = $absensiJadwalModel->insert([
                'id_jadwal'  => $idJadwal,
                'tanggal'    => $tanggal,
                'id_guru'    => $guru['id_guru'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        foreach ($statusArr as $idSiswa => $status) {
            $absensiDetailModel->insert([
                'id_absensi_jadwal' => $idAbsensiJadwal,
                'id_siswa'          => $idSiswa,
                'status'            => $status,
                'keterangan'        => $keteranganArr[$idSiswa] ?? null,
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, absensi tidak tersimpan.']);
        }

        return redirect()->to('/guru/dashboard')->with('success', 'Absensi berhasil disimpan.');
    }
}