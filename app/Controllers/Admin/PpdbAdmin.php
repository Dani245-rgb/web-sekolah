<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PendaftarPpdbModel;
use App\Models\PengaturanPpdbModel;

class PpdbAdmin extends BaseController
{
    protected $ppdbModel;
    protected $settingModel;

    public function __construct()
    {
        $this->ppdbModel    = new PendaftarPpdbModel();
        $this->settingModel = new PengaturanPpdbModel();
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

    public function pengaturan()
    {
        $data['setting'] = $this->settingModel->getSetting();
        return view('admin/ppdb/pengaturan', $data);
    }

    public function updatePengaturan()
    {
        $status = $this->request->getPost('status');
        if (!in_array($status, ['buka', 'tutup'], true)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $this->settingModel->update(1, [
            'status'       => $status,
            'pesan_tutup'  => $this->request->getPost('pesan_tutup'),
            'tahun_ajaran' => $this->request->getPost('tahun_ajaran'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/ppdb/pengaturan')->with('success', 'Pengaturan PPDB berhasil diperbarui.');
    }
}