<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AbsensiDetailModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;

class RekapAbsensi extends BaseController
{
    public function index()
    {
        $kelasModel = new KelasModel();
        $data['kelas'] = $kelasModel->where('status', 'Aktif')->orderBy('nama_kelas', 'ASC')->findAll();

        $idKelas = $this->request->getGet('id_kelas');
        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');

        $absensiDetailModel = new AbsensiDetailModel();
        $data['hasil']   = $idKelas ? $absensiDetailModel->getByKelasTanggal($idKelas, $tanggal) : [];
        $data['idKelas'] = $idKelas;
        $data['tanggal'] = $tanggal;

        return view('admin/rekap-absensi/index', $data);
    }

    public function siswa()
    {
        $keyword = $this->request->getGet('keyword');

        $siswaModel = new SiswaModel();
        $builder = $siswaModel->select('id_siswa, nis, nama, status')->orderBy('nama', 'ASC');
        if ($keyword) {
            $builder->groupStart()->like('nama', $keyword)->orLike('nis', $keyword)->groupEnd();
        }

        $data['siswa']   = $builder->findAll();
        $data['keyword'] = $keyword;

        return view('admin/rekap-absensi/siswa', $data);
    }

    public function siswaDetail($idSiswa)
    {
        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->find($idSiswa);
        if (!$siswa) {
            return redirect()->to('/admin/rekap-absensi/siswa')->with('errors', ['404' => 'Siswa tidak ditemukan.']);
        }

        $tglMulai   = $this->request->getGet('tgl_mulai') ?: date('Y-m-01');
        $tglSelesai = $this->request->getGet('tgl_selesai') ?: date('Y-m-d');

        $absensiDetailModel = new AbsensiDetailModel();
        $rekap  = $absensiDetailModel->rekapPerSiswa($idSiswa, $tglMulai, $tglSelesai);
        $detail = $absensiDetailModel->getBySiswaRange($idSiswa, $tglMulai, $tglSelesai);

        $data['siswa']      = $siswa;
        $data['rekap']      = $rekap;
        $data['detail']     = $detail;
        $data['tglMulai']   = $tglMulai;
        $data['tglSelesai'] = $tglSelesai;

        return view('admin/rekap-absensi/siswa-detail', $data);
    }
}