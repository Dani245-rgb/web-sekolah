<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\AbsensiDetailModel;

class LaporanAkademik extends BaseController
{
    public function index()
    {
        $tab = $this->request->getGet('tab') ?: 'ringkasan';
        $data['tab'] = $tab;

        if ($tab === 'siswa') {
            $data += $this->dataTabSiswa();
        } else {
            $data['tab'] = 'ringkasan';
            $data += $this->dataTabRingkasan();
        }

        return view('admin/laporan_akademik/index', $data);
    }

    private function dataTabRingkasan(): array
    {
        $kelasModel = new KelasModel();
        $daftarKelas = $kelasModel->getAllWithRelasi()->findAll();
        $db = \Config\Database::connect();

        foreach ($daftarKelas as &$k) {
            $rata = $db->table('nilai_siswa')
                ->select('AVG(nilai_siswa.nilai) as rata')
                ->join('komponen_nilai', 'komponen_nilai.id_komponen = nilai_siswa.id_komponen')
                ->join('pengaturan_nilai', 'pengaturan_nilai.id_pengaturan = komponen_nilai.id_pengaturan')
                ->where('pengaturan_nilai.id_kelas', $k['id_kelas'])
                ->get()->getRowArray();
            $k['rata_nilai'] = $rata['rata'] !== null ? round((float) $rata['rata'], 1) : null;

            $totalAbsen = $db->table('absensi_detail')
                ->join('absensi_jadwal', 'absensi_jadwal.id_absensi_jadwal = absensi_detail.id_absensi_jadwal')
                ->join('jadwal', 'jadwal.id_jadwal = absensi_jadwal.id_jadwal')
                ->where('jadwal.id_kelas', $k['id_kelas'])
                ->countAllResults();

            $totalHadir = $db->table('absensi_detail')
                ->join('absensi_jadwal', 'absensi_jadwal.id_absensi_jadwal = absensi_detail.id_absensi_jadwal')
                ->join('jadwal', 'jadwal.id_jadwal = absensi_jadwal.id_jadwal')
                ->where('jadwal.id_kelas', $k['id_kelas'])
                ->where('absensi_detail.status', 'Hadir')
                ->countAllResults();

            $k['persen_hadir'] = $totalAbsen > 0 ? round($totalHadir / $totalAbsen * 100, 1) : null;
            $k['jumlah_siswa'] = $kelasModel->countSiswa($k['id_kelas']);
        }
        unset($k);

        return ['daftarKelas' => $daftarKelas];
    }

    private function dataTabSiswa(): array
    {
        $siswaModel = new SiswaModel();
        $daftarSiswa = $siswaModel->orderBy('nama', 'ASC')->findAll();

        $idSiswa    = $this->request->getGet('id_siswa');
        $tglMulai   = $this->request->getGet('tgl_mulai') ?: date('Y-01-01');
        $tglSelesai = $this->request->getGet('tgl_selesai') ?: date('Y-m-d');

        $detail = null;

        if (!empty($idSiswa)) {
            $db = \Config\Database::connect();

            $siswa = $siswaModel->find($idSiswa);

            $nilaiPerMapel = $db->table('nilai_siswa')
                ->select('mapel.nama_mapel, AVG(nilai_siswa.nilai) as rata')
                ->join('komponen_nilai', 'komponen_nilai.id_komponen = nilai_siswa.id_komponen')
                ->join('pengaturan_nilai', 'pengaturan_nilai.id_pengaturan = komponen_nilai.id_pengaturan')
                ->join('mapel', 'mapel.id_mapel = pengaturan_nilai.id_mapel')
                ->where('nilai_siswa.id_siswa', $idSiswa)
                ->groupBy('mapel.id_mapel')
                ->get()->getResultArray();

            $absensiModel = new AbsensiDetailModel();
            $rekapAbsensi = $absensiModel->rekapPerSiswa($idSiswa, $tglMulai, $tglSelesai);

            $detail = [
                'siswa'           => $siswa,
                'nilai_per_mapel' => $nilaiPerMapel,
                'rekap_absensi'   => $rekapAbsensi,
            ];
        }

        return [
            'daftarSiswa'  => $daftarSiswa,
            'idSiswaAktif' => $idSiswa,
            'tglMulai'     => $tglMulai,
            'tglSelesai'   => $tglSelesai,
            'detail'       => $detail,
        ];
    }
}