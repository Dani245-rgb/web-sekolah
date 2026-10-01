<?php

namespace App\Controllers;

use App\Models\UnduhanModel;

class UnduhanPublik extends BaseController
{
    protected UnduhanModel $unduhanModel;

    public function __construct()
    {
        $this->unduhanModel = new UnduhanModel();
    }

    public function index()
    {
        // Filter dari URL harus berupa teks biasa (?kategori[]=x tidak boleh bikin error)
        $kategori       = $this->request->getGet('kategori');
        $kategoriFilter = is_string($kategori) && trim($kategori) !== '' ? trim($kategori) : null;

        $data['unduhanList']    = $this->unduhanModel->getPublished($kategoriFilter);
        $data['kategoriList']   = $this->unduhanModel->getKategoriList();
        $data['kategoriFilter'] = $kategoriFilter;

        return view('unduhan_publik/index', $data);
    }

    public function download($id)
    {
        if (!ctype_digit((string) $id)) {
            return $this->fileTidakDitemukan();
        }

        $item = $this->unduhanModel->getPublishedById((int) $id);

        if (!$item) {
            return $this->fileTidakDitemukan();
        }

        // basename() mencegah nama file dari database keluar dari folder unduhan
        $path = FCPATH . 'uploads/unduhan/' . basename((string) $item['file']);

        if (!is_file($path)) {
            return redirect()->to('/unduhan')->with('error', 'File fisik tidak ditemukan di server.');
        }

        $this->unduhanModel->tambahJumlahUnduh((int) $id);

        $namaUnduh = basename((string) ($item['nama_file_asli'] ?: $item['file']));

        return $this->response->download($path, null)->setFileName($namaUnduh);
    }

    private function fileTidakDitemukan()
    {
        return redirect()->to('/unduhan')->with('error', 'File tidak ditemukan.');
    }
}