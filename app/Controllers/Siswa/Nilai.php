<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SemesterModel;
use App\Models\TahunAjaranModel;

class Nilai extends BaseController
{
    public function index()
    {
        $idUser = session()->get('id_user');

        $db = \Config\Database::connect();

        $siswa = $db->table('siswa')->where('user_id', $idUser)->get()->getRowArray();

        if (!$siswa) {
            return redirect()->to('/siswa/dashboard')
                ->with('errors', ['nilai' => 'Data siswa tidak ditemukan. Silakan hubungi Admin.']);
        }

        $id_siswa = $siswa['id_siswa'];

        $semesterModel = new SemesterModel();
        $semesterAktif = $semesterModel->where('status', 'Aktif')->first();

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        if (!$semesterAktif || !$tahunAktif) {
            return view('siswa/nilai/index', [
                'hasil'         => [],
                'semesterAktif' => $semesterAktif,
                'pesan'         => 'Tahun ajaran / semester aktif belum diatur oleh Admin.',
            ]);
        }

        // FIX: filter kelas_siswa berdasarkan tahun ajaran aktif juga, bukan cuma id_siswa,
        // supaya tidak nyangkut ke kelas lama kalau siswa pernah pindah/naik kelas.
        $pengaturanList = $db->table('pengaturan_nilai pn')
            ->select('pn.*, mapel.nama_mapel')
            ->join('mapel', 'mapel.id_mapel = pn.id_mapel')
            ->join('kelas_siswa', 'kelas_siswa.id_kelas = pn.id_kelas')
            ->where('kelas_siswa.id_siswa', $id_siswa)
            ->where('kelas_siswa.id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
            ->where('pn.id_semester', $semesterAktif['id_semester'])
            ->where('mapel.ada_nilai', 'Ya') // FIX: skip mapel yang memang tidak butuh nilai
            ->get()->getResultArray();

        $hasil = [];
        foreach ($pengaturanList as $pengaturan) {
            $komponenList = $db->table('komponen_nilai')
                ->where('id_pengaturan', $pengaturan['id_pengaturan'])->get()->getResultArray();

            $bobotMap      = array_column($komponenList, 'bobot', 'id_komponen');
            $totalKomponen = count($bobotMap);

            $nilaiSaya = $totalKomponen
                ? $db->table('nilai_siswa')
                    ->whereIn('id_komponen', array_keys($bobotMap))
                    ->where('id_siswa', $id_siswa)
                    ->get()->getResultArray()
                : [];

            $nilaiAkhir = 0;
            foreach ($nilaiSaya as $row) {
                $nilaiAkhir += $row['nilai'] * ($bobotMap[$row['id_komponen']] ?? 0) / 100;
            }

            // FIX: kalau komponen belum semua diisi guru, jangan tampilkan seolah nilai akhir sudah final
            $lengkap = $totalKomponen > 0 && count($nilaiSaya) === $totalKomponen;

            $hasil[] = [
                'mapel'       => $pengaturan['nama_mapel'],
                'nilai_akhir' => $lengkap ? round($nilaiAkhir, 2) : null,
                'kkm'         => $pengaturan['kkm'],
                'status'      => !$totalKomponen
                    ? 'Belum Diatur'
                    : (!$lengkap
                        ? 'Belum Lengkap'
                        : ($nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas')),
            ];
        }

        return view('siswa/nilai/index', [
            'hasil'         => $hasil,
            'semesterAktif' => $semesterAktif,
            'pesan'         => null,
        ]);
    }
}