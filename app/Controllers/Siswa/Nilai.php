<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SemesterModel;

class Nilai extends BaseController
{
    public function index()
    {
        $idUser = session()->get('id_user');

        $db = \Config\Database::connect();

        // Cari data siswa berdasarkan user_id yang sedang login
        $siswa = $db->table('siswa')->where('user_id', $idUser)->get()->getRowArray();

        if (!$siswa) {
            return redirect()->to('/siswa/dashboard')
                ->with('errors', ['nilai' => 'Data siswa tidak ditemukan. Silakan hubungi Admin.']);
        }

        $id_siswa = $siswa['id_siswa'];

        $semesterModel = new SemesterModel();
        $semesterAktif = $semesterModel->where('status', 'Aktif')->first();

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