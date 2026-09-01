<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\JadwalModel;
use App\Models\TahunAjaranModel;

class Jadwal extends BaseController
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

        $urutanHari    = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $jadwalPerHari = array_fill_keys($urutanHari, []);

        // Cari tahun ajaran aktif
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif        = $tahunAjaranModel->getActive();

        if (!$tahunAktif) {
            return view('siswa/jadwal/index', [
                'siswa'         => $siswa,
                'jadwalPerHari' => $jadwalPerHari,
                'namaKelas'     => null,
                'totalJadwal'   => 0,
                'pesan'         => 'Tahun ajaran aktif belum diatur oleh Admin.',
            ]);
        }

        // Cari kelas siswa ini di tahun ajaran aktif
        $db        = db_connect();
        $kelasRow  = $db->table('kelas_siswa')
            ->select('kelas_siswa.id_kelas, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas')
            ->where('kelas_siswa.id_siswa', $siswa['id_siswa'])
            ->where('kelas_siswa.id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
            ->get()
            ->getRowArray();

        if (!$kelasRow) {
            return view('siswa/jadwal/index', [
                'siswa'         => $siswa,
                'jadwalPerHari' => $jadwalPerHari,
                'namaKelas'     => null,
                'totalJadwal'   => 0,
                'pesan'         => 'Anda belum terdaftar di kelas manapun pada tahun ajaran ini.',
            ]);
        }

        // Ambil semua jadwal kelas ini
        $jadwalModel = new JadwalModel();
        $semuaJadwal = $jadwalModel->select('jadwal.*, mapel.nama_mapel, guru.nama as nama_guru, ruangan.nama_ruangan')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->join('guru', 'guru.id_guru = jadwal.id_guru')
            ->join('ruangan', 'ruangan.id_ruangan = jadwal.id_ruangan')
            ->where('jadwal.id_kelas', $kelasRow['id_kelas'])
            ->where('jadwal.id_semester', $tahunAktif['id_tahun_ajaran'])
            ->orderBy('jadwal.jam_mulai', 'ASC')
            ->findAll();

        foreach ($semuaJadwal as $j) {
            if (isset($jadwalPerHari[$j['hari']])) {
                $jadwalPerHari[$j['hari']][] = $j;
            }
        }

        return view('siswa/jadwal/index', [
            'siswa'         => $siswa,
            'jadwalPerHari' => $jadwalPerHari,
            'namaKelas'     => $kelasRow['nama_kelas'],
            'totalJadwal'   => count($semuaJadwal),
            'pesan'         => null,
        ]);
    }
}