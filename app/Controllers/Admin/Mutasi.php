<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MutasiModel;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;

class Mutasi extends BaseController
{
    protected MutasiModel $mutasiModel;
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected TahunAjaranModel $tahunAjaranModel;

    public function __construct()
    {
        $this->mutasiModel      = new MutasiModel();
        $this->siswaModel       = new SiswaModel();
        $this->kelasModel       = new KelasModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
    }

    public function index()
    {
        $data['mutasi'] = $this->mutasiModel->getAllWithSiswa();
        return view('admin/mutasi/index', $data);
    }

    public function form()
    {
        $data['kelas'] = $this->kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();
        return view('admin/mutasi/form', $data);
    }

    /**
     * Sama persis pola AJAX-nya modul Alumni, tapi khusus buat 1 siswa
     * (di view-nya nanti dipakein radio button, bukan checkbox).
     */
    public function siswaPerKelas($idKelas)
    {
        $tahunAktif = $this->tahunAjaranModel->getActive();

        $siswa = $this->siswaModel
            ->select('siswa.id_siswa, siswa.nis, siswa.nama')
            ->join('kelas_siswa', 'kelas_siswa.id_siswa = siswa.id_siswa')
            ->where('kelas_siswa.id_kelas', $idKelas)
            ->where('kelas_siswa.id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
            ->where('siswa.status', 'Aktif')
            ->orderBy('siswa.nama', 'ASC')
            ->findAll();

        return $this->response->setJSON($siswa);
    }

    /**
     * Catat mutasi 1 siswa + update status siswa jadi 'Pindah' atau 'Keluar'.
     * sekolah_tujuan cuma disimpen kalau jenisnya Pindah.
     */
    public function proses()
    {
        $idSiswa       = $this->request->getPost('id_siswa');
        $jenisMutasi   = $this->request->getPost('jenis_mutasi');
        $tanggalMutasi = $this->request->getPost('tanggal_mutasi');
        $sekolahTujuan = $this->request->getPost('sekolah_tujuan');
        $alasan        = $this->request->getPost('alasan');

        if (empty($idSiswa) || empty($jenisMutasi) || empty($tanggalMutasi)) {
            return redirect()->back()->with('errors', ['pilih' => 'Pilih siswa, jenis mutasi, dan tanggal wajib diisi.']);
        }

        if (!in_array($jenisMutasi, ['Pindah', 'Keluar'], true)) {
            return redirect()->back()->with('errors', ['jenis' => 'Jenis mutasi tidak valid.']);
        }

        $sudahMutasi = $this->mutasiModel->where('id_siswa', $idSiswa)->countAllResults();
        if ($sudahMutasi > 0) {
            return redirect()->back()->with('errors', ['duplikat' => 'Siswa ini sudah tercatat mutasi sebelumnya.']);
        }

        $tahunAktif = $this->tahunAjaranModel->getActive();
        $db = \Config\Database::connect();
        $db->transStart();

        $this->mutasiModel->insert([
            'id_siswa'        => $idSiswa,
            'id_tahun_ajaran' => $tahunAktif['id_tahun_ajaran'],
            'jenis_mutasi'    => $jenisMutasi,
            'tanggal_mutasi'  => $tanggalMutasi,
            'sekolah_tujuan'  => $jenisMutasi === 'Pindah' ? $sekolahTujuan : null,
            'alasan'          => $alasan,
        ]);

        $this->siswaModel->update($idSiswa, ['status' => $jenisMutasi]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, proses dibatalkan.']);
        }

        return redirect()->to('/admin/mutasi')->with('success', 'Siswa berhasil dicatat sebagai ' . $jenisMutasi . '.');
    }

    /**
     * Batal mutasi (jaga-jaga salah klik) — hapus dari tabel mutasi,
     * kembalikan status siswa jadi Aktif. Sama persis pola batal() di Alumni.
     */
    public function batal($idMutasi)
    {
        $mutasi = $this->mutasiModel->find($idMutasi);
        if (!$mutasi) {
            return redirect()->to('/admin/mutasi')->with('errors', ['404' => 'Data tidak ditemukan.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->siswaModel->update($mutasi['id_siswa'], ['status' => 'Aktif']);
        $this->mutasiModel->delete($idMutasi);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/mutasi')->with('errors', ['gagal' => 'Terjadi kesalahan, pembatalan mutasi gagal.']);
        }

        return redirect()->to('/admin/mutasi')->with('success', 'Mutasi dibatalkan, siswa kembali Aktif.');
    }
}
