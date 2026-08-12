<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PendaftarPpdbModel;

class PpdbAdmin extends BaseController
{
    protected $ppdbModel;

    public function __construct()
    {
        $this->ppdbModel = new PendaftarPpdbModel();
    }

    public function index()
    {
        $data['pendaftar'] = $this->ppdbModel->orderBy('created_at', 'DESC')->findAll();
        return view('admin/ppdb/index', $data);
    }

    public function detail($id)
    {
        $data['pendaftar'] = $this->ppdbModel->find($id);

        if (!$data['pendaftar']) {
            return redirect()->to('/admin/ppdb')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/ppdb/detail', $data);
    }

    public function updateStatus($id)
    {
        $pendaftar = $this->ppdbModel->find($id);
        if (!$pendaftar) {
            return redirect()->to('/admin/ppdb')->with('error', 'Data tidak ditemukan.');
        }

        $status = $this->request->getPost('status');
        if (!in_array($status, ['Menunggu', 'Diterima', 'Ditolak'], true)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $this->ppdbModel->update($id, ['status' => $status]);

        return redirect()->to('/admin/ppdb/detail/' . $id)->with('success', 'Status pendaftar berhasil diperbarui.');
    }

    public function delete($id)
    {
        $pendaftar = $this->ppdbModel->find($id);
        if (!$pendaftar) {
            return redirect()->to('/admin/ppdb')->with('error', 'Data tidak ditemukan.');
        }

        $this->ppdbModel->delete($id);

        return redirect()->to('/admin/ppdb')->with('success', 'Data pendaftar berhasil dihapus.');
    }
}