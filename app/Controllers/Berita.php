<?php

namespace App\Controllers;

use App\Models\BeritaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Berita extends BaseController
{
    public function index()
    {
        $beritaModel = new BeritaModel();

        $beritaList = $beritaModel->where('status', 'Published')
            ->orderBy('tanggal_publish', 'DESC')
            ->paginate(9);

        return view('berita_index', [
            'beritaList' => $beritaList,
            'pager'      => $beritaModel->pager,
        ]);
    }

    public function detail($slug)
    {
        $beritaModel = new BeritaModel();
        $berita = $beritaModel->getBySlug($slug);

        if (!$berita) {
            throw PageNotFoundException::forPageNotFound();
        }

        $terkait = $beritaModel->where('status', 'Published')
            ->where('id_berita !=', $berita['id_berita'])
            ->where('kategori', $berita['kategori'])
            ->orderBy('tanggal_publish', 'DESC')
            ->limit(3)
            ->findAll();

        return view('berita_detail', [
            'berita'  => $berita,
            'terkait' => $terkait,
        ]);
    }
}