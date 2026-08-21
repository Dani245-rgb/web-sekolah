<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasSiswaModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;

class KenaikanKelas extends BaseController
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
        return view('admin/kenaikan-kelas/index');
    }

    public function form()
    {
        $data['kelas']      = $this->kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();
        $data['tahunAjaran'] = $this->tahunAjaranModel->orderBy('tahun_ajaran', 'DESC')->findAll();
        $data['tahunAktif']  = $this->tahunAjaranModel->getActive();
        return view('admin/kenaikan-kelas/form', $data);
    }

    /**
     * AJAX: daftar siswa Aktif di kelas asal, tahun ajaran asal.
     */
    public function siswaByKelas($idKelas, $idTahunAjaran)
    {
        $siswa = $this->kelasSiswaModel->getSiswaByKelas($idKelas, $idTahunAjaran);
        return $this->response->setJSON($siswa);
    }

    /**
     * Proses kenaikan kelas massal.
     */
  public function proses()
{
    $idSiswaArr          = $this->request->getPost('id_siswa'); // array checkbox
    $idKelasTujuan       = $this->request->getPost('id_kelas_tujuan');
    $idTahunAjaranTujuan = $this->request->getPost('id_tahun_ajaran_tujuan');

    if (empty($idSiswaArr) || !is_array($idSiswaArr) || empty($idKelasTujuan) || empty($idTahunAjaranTujuan)) {
        return redirect()->back()->with('errors', ['pilih' => 'Pilih kelas tujuan, tahun ajaran tujuan, dan minimal 1 siswa.']);
    }

    $kelasTujuanValid = $this->kelasModel->find($idKelasTujuan);
    if (!$kelasTujuanValid) {
        return redirect()->back()->with('errors', ['pilih' => 'Kelas tujuan tidak valid.']);
    }

    $tahunAjaranTujuanValid = $this->tahunAjaranModel->find($idTahunAjaranTujuan);
    if (!$tahunAjaranTujuanValid) {
        return redirect()->back()->with('errors', ['pilih' => 'Tahun ajaran tujuan tidak valid.']);
    }

    $db = \Config\Database::connect();
    $db->transStart();

    $sudahTerdaftar = $this->kelasSiswaModel
        ->select('id_siswa')
        ->whereIn('id_siswa', $idSiswaArr)
        ->where('id_tahun_ajaran', $idTahunAjaranTujuan)
        ->findColumn('id_siswa') ?? [];

    $gagal = [];
    foreach ($idSiswaArr as $idSiswa) {
        if (in_array($idSiswa, $sudahTerdaftar)) {
            $gagal[] = $idSiswa;
            continue;
        }

        $idInsert = $this->kelasSiswaModel->insert([
            'id_kelas'        => $idKelasTujuan,
            'id_siswa'        => $idSiswa,
            'id_tahun_ajaran' => $idTahunAjaranTujuan,
        ]);

        if (!$idInsert) {
            $gagal[] = $idSiswa;
            continue;
        }
    }

    $db->transComplete();

    if ($db->transStatus() === false) {
        return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, proses dibatalkan.']);
    }

    $pesan = count($idSiswaArr) - count($gagal) . ' siswa berhasil naik kelas.';
    if (!empty($gagal)) {
        $pesan .= ' (' . count($gagal) . ' siswa dilewati karena sudah punya kelas di tahun ajaran tujuan.)';
    }

    return redirect()->to('/admin/kenaikan-kelas/form')->with('success', $pesan);
    }   

}