<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AlumniModel;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;

class Alumni extends BaseController
{
    protected AlumniModel $alumniModel;
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected TahunAjaranModel $tahunAjaranModel;

    public function __construct()
    {
        $this->alumniModel      = new AlumniModel();
        $this->siswaModel       = new SiswaModel();
        $this->kelasModel       = new KelasModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
    }

    public function index()
    {
        $data['alumni'] = $this->alumniModel->getAllWithSiswa();
        return view('admin/alumni/index', $data);
    }

    /**
     * Form pilih siswa mana yang mau diluluskan — muat 2 mode sekaligus:
     * bisa centang 1 siswa aja, atau centang banyak siswa dari kelas yang sama.
     */
    public function form()
    {
        $tahunAktif = $this->tahunAjaranModel->getActive();

        $data['kelas']      = $this->kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();
        $data['tahunAktif'] = $tahunAktif;

        return view('admin/alumni/form', $data);
    }

    /**
     * Ambil daftar siswa Aktif di 1 kelas (dipanggil AJAX dari form, atau load biasa).
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
     * Proses meluluskan — terima array id_siswa (bisa 1 atau banyak, dari checkbox).
     * Insert ke tabel alumni + update status siswa jadi 'Lulus', dibungkus transaction
     * biar kalau ada yang gagal di tengah, semua di-rollback (gak ada siswa "setengah lulus").
     */
    public function proses()
    {
        $idSiswaList  = $this->request->getPost('id_siswa'); // array
        $tanggalLulus = $this->request->getPost('tanggal_lulus');

        if (empty($idSiswaList) || empty($tanggalLulus)) {
            return redirect()->back()->with('errors', ['pilih' => 'Pilih minimal 1 siswa dan isi tanggal lulus.']);
        }

        $tahunAktif = $this->tahunAjaranModel->getActive();
        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($idSiswaList as $idSiswa) {
            // Skip diam-diam kalau siswa ini udah jadi alumni sebelumnya (jaga-jaga double klik)
            $sudahAlumni = $this->alumniModel->where('id_siswa', $idSiswa)->countAllResults();
            if ($sudahAlumni > 0) continue;

            $this->alumniModel->insert([
                'id_siswa'        => $idSiswa,
                'id_tahun_ajaran' => $tahunAktif['id_tahun_ajaran'],
                'tanggal_lulus'   => $tanggalLulus,
            ]);

            $this->siswaModel->update($idSiswa, ['status' => 'Lulus']);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, proses dibatalkan.']);
        }

        return redirect()->to('/admin/alumni')->with('success', count($idSiswaList) . ' siswa berhasil diluluskan.');
    }

    /**
     * Batal luluskan (jaga-jaga kalau Admin salah klik) — hapus dari tabel alumni,
     * kembalikan status siswa jadi Aktif.
     */
    public function batal($idAlumni)
    {
        $alumni = $this->alumniModel->find($idAlumni);
        if (!$alumni) {
            return redirect()->to('/admin/alumni')->with('errors', ['404' => 'Data tidak ditemukan.']);
        }

        $this->siswaModel->update($alumni['id_siswa'], ['status' => 'Aktif']);
        $this->alumniModel->delete($idAlumni);

        return redirect()->to('/admin/alumni')->with('success', 'Kelulusan dibatalkan, siswa kembali Aktif.');
    }
}