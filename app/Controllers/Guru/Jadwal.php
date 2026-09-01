<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\JadwalModel;

class Jadwal extends BaseController
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

        $jadwalModel = new JadwalModel();
        $semuaJadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->where('jadwal.id_guru', $guru['id_guru'])
            ->orderBy('jadwal.jam_mulai', 'ASC')
            ->findAll();

        // Kelompokkan per hari, urut Senin - Minggu
        $urutanHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $jadwalPerHari = array_fill_keys($urutanHari, []);

        foreach ($semuaJadwal as $j) {
            if (isset($jadwalPerHari[$j['hari']])) {
                $jadwalPerHari[$j['hari']][] = $j;
            }
        }

        return view('guru/jadwal/index', [
            'guru'          => $guru,
            'jadwalPerHari' => $jadwalPerHari,
            'totalJadwal'   => count($semuaJadwal),
        ]);
    }
}