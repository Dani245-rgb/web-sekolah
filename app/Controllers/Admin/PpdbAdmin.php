<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PendaftarPpdbModel;
use App\Models\PengaturanPpdbModel;

class PpdbAdmin extends BaseController
{
    protected PendaftarPpdbModel $ppdbModel;
    protected PengaturanPpdbModel $settingModel;

    public function __construct()
    {
        helper('teks');

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

        $status = (string) $this->request->getPost('status');

        if (!in_array($status, PendaftarPpdbModel::STATUS, true)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        if (!$this->ppdbModel->ubahStatus((int) $id, $status)) {
            return redirect()->back()->with('error', 'Status gagal disimpan.');
        }

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
        $rules = [
            'status'       => 'required|in_list[' . implode(',', PengaturanPpdbModel::STATUS) . ']',
            'pesan_tutup'  => 'permit_empty|max_length[255]',
            'tahun_ajaran' => 'permit_empty|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $status      = (string) $this->request->getPost('status');
        $tahunAjaran = rapikan_teks((string) $this->request->getPost('tahun_ajaran'));

        // Pendaftaran tidak boleh dibuka tanpa tahun ajaran, supaya data pendaftar tercatat lengkap
        if ($status === 'buka' && $tahunAjaran === '') {
            return redirect()->back()->withInput()
                ->with('error', 'Isi tahun ajaran dulu sebelum membuka pendaftaran.');
        }

        $berhasil = $this->settingModel->simpan([
            'status'       => $status,
            'pesan_tutup'  => $this->request->getPost('pesan_tutup'),
            'tahun_ajaran' => $tahunAjaran !== '' ? $tahunAjaran : null,
        ]);

        if (!$berhasil) {
            return redirect()->back()->withInput()->with('error', 'Pengaturan PPDB gagal disimpan.');
        }

        return redirect()->to('/admin/ppdb/pengaturan')->with('success', 'Pengaturan PPDB berhasil diperbarui.');
    }
}