<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PklPenempatanModel;
use App\Models\SiswaModel;
use App\Models\GuruModel;

class Pkl extends BaseController
{
    public function index()
    {
        $pklModel = new PklPenempatanModel();

        $filter = [
            'keyword' => $this->request->getGet('keyword'),
            'status'  => $this->request->getGet('status'),
        ];

        $daftar = $pklModel->getAllWithRelasi($filter);

        return view('admin/pkl/index', [
            'daftar' => $daftar,
            'filter' => $filter,
        ]);
    }

    public function create()
    {
        $siswaModel = new SiswaModel();
        $guruModel  = new GuruModel();

        return view('admin/pkl/form', [
            'siswaList' => $siswaModel->orderBy('nama', 'ASC')->findAll(),
            'guruList'  => $guruModel->where('status', 'Aktif')->orderBy('nama', 'ASC')->findAll(),
            'penempatan' => null,
        ]);
    }

    public function store()
    {
        $pklModel = new PklPenempatanModel();

        $data = [
            'id_siswa'                  => $this->request->getPost('id_siswa'),
            'id_guru_pembimbing'        => $this->request->getPost('id_guru_pembimbing') ?: null,
            'nama_perusahaan'           => $this->request->getPost('nama_perusahaan'),
            'alamat_perusahaan'         => $this->request->getPost('alamat_perusahaan'),
            'nama_pembimbing_industri'  => $this->request->getPost('nama_pembimbing_industri'),
            'no_hp_pembimbing_industri' => $this->request->getPost('no_hp_pembimbing_industri'),
            'tanggal_mulai'             => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai'           => $this->request->getPost('tanggal_selesai') ?: null,
            'status'                    => $this->request->getPost('status'),
            'catatan'                   => $this->request->getPost('catatan'),
        ];

        if (!$pklModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $pklModel->errors());
        }

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $pklModel   = new PklPenempatanModel();
        $siswaModel = new SiswaModel();
        $guruModel  = new GuruModel();

        $penempatan = $pklModel->find($id);
        if (!$penempatan) {
            return redirect()->to('/admin/pkl')->with('errors', ['404' => 'Data penempatan tidak ditemukan.']);
        }

        return view('admin/pkl/form', [
            'siswaList'  => $siswaModel->orderBy('nama', 'ASC')->findAll(),
            'guruList'   => $guruModel->where('status', 'Aktif')->orderBy('nama', 'ASC')->findAll(),
            'penempatan' => $penempatan,
        ]);
    }

    public function update($id)
    {
        $pklModel = new PklPenempatanModel();

        $penempatan = $pklModel->find($id);
        if (!$penempatan) {
            return redirect()->to('/admin/pkl')->with('errors', ['404' => 'Data penempatan tidak ditemukan.']);
        }

        $data = [
            'id_siswa'                  => $this->request->getPost('id_siswa'),
            'id_guru_pembimbing'        => $this->request->getPost('id_guru_pembimbing') ?: null,
            'nama_perusahaan'           => $this->request->getPost('nama_perusahaan'),
            'alamat_perusahaan'         => $this->request->getPost('alamat_perusahaan'),
            'nama_pembimbing_industri'  => $this->request->getPost('nama_pembimbing_industri'),
            'no_hp_pembimbing_industri' => $this->request->getPost('no_hp_pembimbing_industri'),
            'tanggal_mulai'             => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai'           => $this->request->getPost('tanggal_selesai') ?: null,
            'status'                    => $this->request->getPost('status'),
            'catatan'                   => $this->request->getPost('catatan'),
        ];

        if (!$pklModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $pklModel->errors());
        }

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil diperbarui.');
    }

    public function delete($id)
    {
        $pklModel = new PklPenempatanModel();

        if (!$pklModel->find($id)) {
            return redirect()->to('/admin/pkl')->with('errors', ['404' => 'Data penempatan tidak ditemukan.']);
        }

        $pklModel->delete($id); // jurnal ikut terhapus (ON DELETE CASCADE)

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil dihapus.');
    }
}