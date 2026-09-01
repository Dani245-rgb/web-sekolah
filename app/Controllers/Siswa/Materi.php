<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasSiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\MateriModel;

class Materi extends BaseController
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

    protected function redirectSiswaTidakDitemukan()
    {
        return redirect()->to('/logout')
            ->with('errors', ['akun' => 'Data siswa Anda tidak ditemukan. Silakan hubungi Admin.']);
    }

    protected function getKelasAktif($siswa)
    {
        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();
        if (!$tahunAktif) {
            return null;
        }

        $kelasSiswaModel = new KelasSiswaModel();
        $relasi = $kelasSiswaModel
            ->where('id_siswa', $siswa['id_siswa'])
            ->where('id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
            ->first();

        return $relasi ? $relasi['id_kelas'] : null;
    }

    public function index()
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectSiswaTidakDitemukan();
        }

        $idKelas = $this->getKelasAktif($siswa);
        if (!$idKelas) {
            return view('siswa/materi/index', [
                'daftar' => [],
                'errors' => 'Anda belum terdaftar di kelas aktif manapun.',
            ]);
        }

        $materiModel = new MateriModel();
        $daftar = $materiModel->getByKelas($idKelas);

        return view('siswa/materi/index', [
            'daftar' => $daftar,
        ]);
    }

    // Method download() dipindah — sekarang pakai endpoint terpadu Guru\Materi::unduhFile()
    // yang menangani otorisasi untuk guru DAN siswa sekaligus. Lihat Routes.php.
}