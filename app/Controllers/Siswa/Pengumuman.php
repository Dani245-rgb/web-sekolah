<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\PengumumanModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Pengumuman extends BaseController
{
    public function index()
    {
        $pengumumanModel = new PengumumanModel();

        $daftar = $pengumumanModel->where('status', 'Published')
            ->orderBy('tanggal_publish', 'DESC')
            ->paginate(10);

        return view('siswa/pengumuman/index', [
            'daftar' => $daftar,
            'pager'  => $pengumumanModel->pager,
        ]);
    }

    public function detail($slug)
    {
        $pengumumanModel = new PengumumanModel();
        $pengumuman = $pengumumanModel->getBySlug($slug);

        if (!$pengumuman) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('siswa/pengumuman/detail', [
            'pengumuman' => $pengumuman,
        ]);
    }
}