<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengaturanNilaiModel;
use App\Models\KomponenNilaiModel;
use App\Models\NilaiSiswaModel;
use App\Models\SemesterModel;

class RekapNilai extends BaseController
{
    public function index()
    {
        $semesterModel = new SemesterModel();
        $semesterAktif = $semesterModel->where('status', 'Aktif')->first();

        $db = \Config\Database::connect();
        $builder = $db->table('pengaturan_nilai pn')
            ->select('pn.*, guru.nama as nama_guru, kelas.nama_kelas, mapel.nama_mapel')
            ->join('guru', 'guru.id_guru = pn.id_guru')   // TODO: sesuaikan nama kolom nama_guru
            ->join('kelas', 'kelas.id_kelas = pn.id_kelas')
            ->join('mapel', 'mapel.id_mapel = pn.id_mapel')
            ->where('pn.id_semester', $semesterAktif['id_semester']);

        if ($id_kelas = $this->request->getGet('id_kelas')) $builder->where('pn.id_kelas', $id_kelas);
        if ($id_mapel = $this->request->getGet('id_mapel')) $builder->where('pn.id_mapel', $id_mapel);
        if ($id_guru  = $this->request->getGet('id_guru'))  $builder->where('pn.id_guru', $id_guru);

        $daftarPengaturan = $builder->get()->getResultArray();

        return view('admin/nilai/rekap', compact('daftarPengaturan', 'semesterAktif'));
    }

    public function detail($id_pengaturan)
    {
        $pengaturanModel = new PengaturanNilaiModel();
        $komponenModel   = new KomponenNilaiModel();
        $nilaiModel      = new NilaiSiswaModel();

        $pengaturan   = $pengaturanModel->find($id_pengaturan);
        $komponenList = $komponenModel->where('id_pengaturan', $id_pengaturan)->findAll();
        $bobotMap     = array_column($komponenList, 'bobot', 'id_komponen');

        $db = \Config\Database::connect();
        $rows = $db->table('nilai_siswa ns')
            ->select('ns.*, siswa.nama as nama_siswa')
            ->join('siswa', 'siswa.id_siswa = ns.id_siswa')
            ->whereIn('ns.id_komponen', array_keys($bobotMap))
            ->get()->getResultArray();

        $perSiswa = [];
        foreach ($rows as $row) {
            $perSiswa[$row['id_siswa']]['nama_siswa'] = $row['nama_siswa'];
            $perSiswa[$row['id_siswa']]['nilai'][] = $row;
        }

        $hasil = [];
        foreach ($perSiswa as $data) {
            $nilaiAkhir = 0;
            foreach ($data['nilai'] as $row) {
                $nilaiAkhir += $row['nilai'] * ($bobotMap[$row['id_komponen']] ?? 0) / 100;
            }
            $hasil[] = [
                'nama_siswa'  => $data['nama_siswa'],
                'nilai_akhir' => round($nilaiAkhir, 2),
                'status'      => $nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas',
            ];
        }

        return view('admin/nilai/detail', compact('pengaturan', 'hasil'));
    }
}