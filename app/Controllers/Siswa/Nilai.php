<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SemesterModel;

class Nilai extends BaseController
{
    public function index()
    {
        $id_siswa      = session()->get('id_siswa'); // TODO: sesuaikan cara ambil id_siswa yang login
        $semesterModel = new SemesterModel();
        $semesterAktif = $semesterModel->where('status', 'Aktif')->first();

        // TODO: sesuaikan nama tabel pivot kelas_siswa & kolom nama_mapel
        $db = \Config\Database::connect();
        $pengaturanList = $db->table('pengaturan_nilai pn')
            ->select('pn.*, mapel.nama_mapel')
            ->join('mapel', 'mapel.id_mapel = pn.id_mapel')
            ->join('kelas_siswa', 'kelas_siswa.id_kelas = pn.id_kelas')
            ->where('kelas_siswa.id_siswa', $id_siswa)
            ->where('pn.id_semester', $semesterAktif['id_semester'])
            ->get()->getResultArray();

        $hasil = [];
        foreach ($pengaturanList as $pengaturan) {
            $komponenList = $db->table('komponen_nilai')
                ->where('id_pengaturan', $pengaturan['id_pengaturan'])->get()->getResultArray();
            $bobotMap = array_column($komponenList, 'bobot', 'id_komponen');

            $nilaiSaya = $db->table('nilai_siswa')
                ->whereIn('id_komponen', array_keys($bobotMap))
                ->where('id_siswa', $id_siswa)
                ->get()->getResultArray();

            $nilaiAkhir = 0;
            foreach ($nilaiSaya as $row) {
                $nilaiAkhir += $row['nilai'] * ($bobotMap[$row['id_komponen']] ?? 0) / 100;
            }

            $hasil[] = [
                'mapel'       => $pengaturan['nama_mapel'],
                'nilai_akhir' => round($nilaiAkhir, 2),
                'kkm'         => $pengaturan['kkm'],
                'status'      => $nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas',
            ];
        }

        return view('siswa/nilai/index', compact('hasil', 'semesterAktif'));
    }
}