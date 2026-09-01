<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\AbsensiDetailModel;

class Absensi extends BaseController
{
    public function index()
    {
        $idUser = session()->get('id_user');

        $db    = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('user_id', $idUser)->get()->getRowArray();

        if (!$siswa) {
            return redirect()->to('/siswa/dashboard')
                ->with('errors', ['absensi' => 'Data siswa tidak ditemukan. Silakan hubungi Admin.']);
        }

        $id_siswa = $siswa['id_siswa'];

        // Filter bulan, default bulan berjalan. Format dari <input type="month"> = 'YYYY-MM'
        $bulanInput = $this->request->getGet('bulan') ?: date('Y-m');

        // Validasi format YYYY-MM, fallback ke bulan berjalan kalau tidak valid
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanInput)) {
            $bulanInput = date('Y-m');
        }

        $tglMulai   = $bulanInput . '-01';
        $tglSelesai = date('Y-m-t', strtotime($tglMulai));

        $absensiModel = new AbsensiDetailModel();

        $riwayat = $absensiModel->getBySiswaRange($id_siswa, $tglMulai, $tglSelesai);
        $rekap   = $absensiModel->rekapPerSiswa($id_siswa, $tglMulai, $tglSelesai);

        $totalTercatat = array_sum($rekap);
        $persenHadir = $totalTercatat > 0 ? round((($rekap['Hadir'] ?? 0) / $totalTercatat) * 100, 1) : 0;

        return view('siswa/absensi/index', [
            'siswa'         => $siswa,
            'riwayat'       => $riwayat,
            'rekap'         => $rekap,
            'persenHadir'   => $persenHadir,
            'bulanDipilih'  => $bulanInput,
        ]);
    }
}