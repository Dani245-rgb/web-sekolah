<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\GuruModel;
use App\Models\PengumumanModel;
use Config\Database;

class Dashboard extends BaseController
{
    public function index()
    {
        $siswaModel      = new SiswaModel();
        $guruModel       = new GuruModel();
        $pengumumanModel = new PengumumanModel();
        $db              = Database::connect();

        // Statistik ringkas
        $totalSiswa = $siswaModel->countAllResults();
        $totalGuru  = $guruModel->countAllResults();

        // Siswa per gender
        $perGenderRaw = $siswaModel->select('jenis_kelamin, COUNT(*) as jumlah')
            ->groupBy('jenis_kelamin')
            ->findAll();

        $perGender = ['L' => 0, 'P' => 0];
        foreach ($perGenderRaw as $row) {
            if (isset($perGender[$row['jenis_kelamin']])) {
                $perGender[$row['jenis_kelamin']] = (int) $row['jumlah'];
            }
        }

        // 5 notifikasi terbaru
        $notifikasiTerbaru = $db->table('notifikasi')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Pengumuman terbaru
        $pengumumanTerbaru = $pengumumanModel->getTerbaru(5);

        // Aktivitas terbaru (audit log)
        $aktivitasTerbaru = $db->table('audit_log')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $data = [
            'title'             => 'Dashboard',
            'totalSiswa'        => $totalSiswa,
            'totalGuru'         => $totalGuru,
            'perGender'         => $perGender,
            'notifikasiTerbaru' => $notifikasiTerbaru,
            'pengumumanTerbaru' => $pengumumanTerbaru,
            'aktivitasTerbaru'  => $aktivitasTerbaru,
        ];

        return view('admin/dashboard/index', $data);
    }
}