<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\LaporanSiswaService;

class LaporanSiswa extends BaseController
{
    protected LaporanSiswaService $laporan;

    public function __construct()
    {
        $this->laporan = service('laporanSiswaService');
    }

    public function index()
    {
        $siswa = $this->laporan->ambilSiswa();

        $data['siswa']          = $siswa;
        $data['totalPerGender'] = $this->laporan->hitungPerGender($siswa);
        $data['totalSiswa']     = count($siswa);

        return view('admin/laporan_siswa/index', $data);
    }

    public function exportExcel()
    {
        $isi = $this->laporan->buatExcel($this->laporan->ambilSiswa());

        return $this->response->download('laporan_siswa_' . date('Y-m-d_His') . '.xlsx', $isi, true);
    }
}