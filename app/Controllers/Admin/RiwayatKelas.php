<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasSiswaModel;
use App\Models\SiswaModel;

class RiwayatKelas extends BaseController
{
    protected KelasSiswaModel $kelasSiswaModel;
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->kelasSiswaModel = new KelasSiswaModel();
        $this->siswaModel      = new SiswaModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('keyword');

        $builder = $this->siswaModel->select('id_siswa, nis, nama, status')->orderBy('nama', 'ASC');
        if ($keyword) {
            $builder->groupStart()
                ->like('nama', $keyword)
                ->orLike('nis', $keyword)
                ->groupEnd();
        }

        $data['siswa']   = $builder->findAll();
        $data['keyword'] = $keyword;

        return view('admin/riwayat-kelas/index', $data);
    }

    public function detail($idSiswa)
    {
        $siswa = $this->siswaModel->find($idSiswa);
        if (!$siswa) {
            return redirect()->to('/admin/riwayat-kelas')->with('errors', ['404' => 'Siswa tidak ditemukan.']);
        }

        $data['siswa']   = $siswa;
        $data['riwayat'] = $this->kelasSiswaModel->getRiwayatBySiswa($idSiswa);

        return view('admin/riwayat-kelas/detail', $data);
    }
}