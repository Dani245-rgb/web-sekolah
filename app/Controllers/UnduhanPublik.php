<?php

namespace App\Controllers;

use App\Models\UnduhanModel;

class UnduhanPublik extends BaseController
{
    protected $unduhanModel;

    public function __construct()
    {
        $this->unduhanModel = new UnduhanModel();
    }

    public function index()
    {
        $kategoriFilter = $this->request->getGet('kategori');

        $data['unduhanList']    = $this->unduhanModel->getPublished($kategoriFilter);
        $data['kategoriList']   = $this->unduhanModel->getKategoriList();
        $data['kategoriFilter'] = $kategoriFilter;

        return view('unduhan_publik/index', $data);
    }

    public function download($id)
    {
        if (!is_numeric($id)) {
            return redirect()->to('/unduhan')->with('error', 'File tidak ditemukan.');
        }

        $item = $this->unduhanModel->where('status', 'Published')->find((int) $id);

        if (!$item) {
            return redirect()->to('/unduhan')->with('error', 'File tidak ditemukan.');
        }

        $path = FCPATH . 'uploads/unduhan/' . $item['file'];

        if (!is_file($path)) {
            return redirect()->to('/unduhan')->with('error', 'File fisik tidak ditemukan di server.');
        }

        $this->unduhanModel->tambahJumlahUnduh((int) $id);

        $namaUnduh = $item['nama_file_asli'] ?: $item['file'];

        return $this->response->download($path, null)->setFileName($namaUnduh);
    }
}