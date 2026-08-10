<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasSiswaModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;

class AssignKelas extends BaseController
{
    protected KelasSiswaModel $kelasSiswaModel;
    protected KelasModel $kelasModel;
    protected TahunAjaranModel $tahunAjaranModel;

    public function __construct()
    {
        $this->kelasSiswaModel  = new KelasSiswaModel();
        $this->kelasModel       = new KelasModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
    }

  public function index()
{
    $tahunAjaranModel = new \App\Models\TahunAjaranModel();
    $tahunAktif = $this->tahunAjaranModel->getActive();

    // Kalau ada filter dari GET, pakai itu. Kalau tidak, default ke tahun aktif.
    $idFilter = $this->request->getGet('id_tahun_ajaran') ?: ($tahunAktif['id_tahun_ajaran'] ?? null);

    $data['assignments']  = $this->kelasSiswaModel->getAllWithDetail($idFilter);
    $data['tahunAktif']   = $tahunAktif;
    $data['tahunAjaran']  = $this->tahunAjaranModel->orderBy('tahun_ajaran', 'DESC')->findAll();
    $data['idFilter']     = $idFilter;

    return view('admin/assign-kelas/index', $data);
}   

    public function form()
    {
        $data['kelas']      = $this->kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();
        $data['tahunAktif'] = $this->tahunAjaranModel->getActive();
        return view('admin/assign-kelas/form', $data);
    }

    /**
     * AJAX: daftar siswa Aktif yang belum punya kelas di tahun ajaran aktif.
     * Dipanggil dari form, tidak tergantung id_kelas manapun -
     * karena yang dicek adalah "belum di-assign ke kelas manapun", bukan per kelas.
     */
    public function siswaBelumAssign()
    {
        $tahunAktif = $this->tahunAjaranModel->getActive();

        if (!$tahunAktif) {
            return $this->response->setJSON([]);
        }

        $siswa = $this->kelasSiswaModel->getSiswaBelumAssign($tahunAktif['id_tahun_ajaran']);
        return $this->response->setJSON($siswa);
    }

    /**
     * Assign massal - banyak siswa ke 1 kelas, tahun ajaran aktif.
     */
    public function proses()
    {
        $idKelas    = $this->request->getPost('id_kelas');
        $idSiswaArr = $this->request->getPost('id_siswa'); // array dari checkbox

        if (empty($idKelas) || empty($idSiswaArr) || !is_array($idSiswaArr)) {
            return redirect()->back()->with('errors', ['pilih' => 'Pilih kelas dan minimal 1 siswa.']);
        }

        $tahunAktif = $this->tahunAjaranModel->getActive();
        if (!$tahunAktif) {
            return redirect()->back()->with('errors', ['tahun' => 'Belum ada tahun ajaran aktif.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $gagal = [];
        foreach ($idSiswaArr as $idSiswa) {
            // Jaga-jaga race condition / siswa yang keburu di-assign di tab lain
            $sudahAda = $this->kelasSiswaModel
                ->where('id_siswa', $idSiswa)
                ->where('id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
                ->countAllResults();

            if ($sudahAda > 0) {
                $gagal[] = $idSiswa;
                continue;
            }

            $this->kelasSiswaModel->insert([
                'id_kelas'        => $idKelas,
                'id_siswa'        => $idSiswa,
                'id_tahun_ajaran' => $tahunAktif['id_tahun_ajaran'],
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, proses dibatalkan.']);
        }

        $pesan = count($idSiswaArr) - count($gagal) . ' siswa berhasil di-assign ke kelas.';
        if (!empty($gagal)) {
            $pesan .= ' (' . count($gagal) . ' siswa dilewati karena sudah punya kelas di tahun ajaran ini.)';
        }

        return redirect()->to('/admin/assign-kelas')->with('success', $pesan);
    }

    /**
     * Batal assignment - hapus baris kelas_siswa (siswa kembali "belum assign").
     */
    public function batal($idKelasSiswa)
    {
        $row = $this->kelasSiswaModel->find($idKelasSiswa);
        if (!$row) {
            return redirect()->to('/admin/assign-kelas')->with('errors', ['404' => 'Data tidak ditemukan.']);
        }

        $this->kelasSiswaModel->delete($idKelasSiswa);
        return redirect()->to('/admin/assign-kelas')->with('success', 'Assignment kelas dibatalkan.');
    }
}