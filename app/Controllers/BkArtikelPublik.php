<?php

namespace App\Controllers;

use App\Models\BkArtikelModel;

class BkArtikelPublik extends BaseController
{
    protected $artikelModel;

    protected $kategoriLabel = [
        'kesehatan_mental' => 'Kesehatan Mental',
        'karier'           => 'Karier & Studi Lanjut',
        'tes_minat'        => 'Tes Minat & Bakat',
    ];

    public function __construct()
    {
        $this->artikelModel = new BkArtikelModel();
    }

    public function kesehatanMental()
    {
        return $this->tampilkanKategori('kesehatan_mental');
    }

    public function karier()
    {
        return $this->tampilkanKategori('karier');
    }

    public function tesMinat()
    {
        return $this->tampilkanKategori('tes_minat');
    }

    public function detail(string $slug)
    {
        $item = $this->artikelModel->getBySlug($slug);

        if (!$item) {
            return redirect()->to('/bk/kesehatan-mental')->with('error', 'Artikel tidak ditemukan.');
        }

        $data['item']  = $item;
        $data['label'] = $this->kategoriLabel[$item['kategori']] ?? $item['kategori'];

        return view('bk_artikel_publik/detail', $data);
    }

    private function tampilkanKategori(string $kategori)
    {
        $data['artikelList'] = $this->artikelModel->getPublishedByKategori($kategori);
        $data['kategori']    = $kategori;
        $data['label']       = $this->kategoriLabel[$kategori] ?? $kategori;

        return view('bk_artikel_publik/index', $data);
    }
}