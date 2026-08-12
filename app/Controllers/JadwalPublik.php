<?php

namespace App\Controllers;

use App\Models\JadwalModel;
use App\Models\KelasModel;

class JadwalPublik extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Ambil semester yang sedang aktif
        $semesterAktif = $db->table('semester')->where('status', 'Aktif')->get()->getRowArray();

        if (!$semesterAktif) {
            return view('jadwal_publik/index', [
                'jadwal'      => [],
                'kelasList'   => [],
                'filter'      => [],
                'semesterAda' => false,
            ]);
        }

        $jadwalModel = new JadwalModel();
        $kelasModel  = new KelasModel();

        $filter = [
            'id_kelas' => $this->request->getGet('id_kelas'),
            'hari'     => $this->request->getGet('hari'),
        ];

        $data['jadwal']      = $jadwalModel->getAllWithRelasi($semesterAktif['id_semester'], $filter);
        $data['kelasList']   = $kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        $data['filter']      = $filter;
        $data['semesterAda'] = true;

        return view('jadwal_publik/index', $data);
    }
}