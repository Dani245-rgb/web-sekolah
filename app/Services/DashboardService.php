<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\GuruModel;
use App\Models\NotifikasiModel;
use App\Models\PengumumanModel;
use App\Models\SiswaModel;

/**
 * Data ringkasan untuk halaman Dashboard admin.
 */
class DashboardService
{
    public function __construct(
        protected SiswaModel $siswaModel,
        protected GuruModel $guruModel,
        protected PengumumanModel $pengumumanModel,
        protected NotifikasiModel $notifikasiModel,
        protected AuditLogModel $auditLogModel,
    ) {
    }

        public function ringkasan(int $idUser, bool $superadmin, bool $penuh = true): array
    {
        $data = [
            'penuh'             => $penuh,
            'totalSiswa'        => 0,
            'totalGuru'         => 0,
            'perGender'         => ['L' => 0, 'P' => 0],
            'notifikasiTerbaru' => [],
            'pengumumanTerbaru' => $this->pengumumanModel->getTerbaru(5),
            'aktivitasTerbaru'  => [],
            'tampilAktivitas'   => false,
        ];

        // Content Manager: hanya ringkasan konten website, tanpa data siswa/guru/notifikasi/audit.
        if (!$penuh) {
            return $data;
        }

        $data['totalSiswa']        = $this->siswaModel->countAllResults();
        $data['totalGuru']         = $this->guruModel->countAllResults();
        $data['perGender']         = $this->siswaPerGender();
        $data['notifikasiTerbaru'] = $this->notifikasiModel->getForUser($idUser, 5);
        // Audit log hanya boleh dilihat superadmin, sama seperti halaman Audit Log
        $data['aktivitasTerbaru']  = $superadmin
            ? $this->auditLogModel->orderBy('created_at', 'DESC')->findAll(5)
            : [];
        $data['tampilAktivitas']   = $superadmin;

        return $data;
    }

    /**
     * Jumlah siswa per jenis kelamin, contoh: ['L' => 120, 'P' => 98].
     */
    private function siswaPerGender(): array
    {
        $baris = $this->siswaModel->select('jenis_kelamin, COUNT(*) as jumlah')
            ->groupBy('jenis_kelamin')
            ->findAll();

        $perGender = ['L' => 0, 'P' => 0];

        foreach ($baris as $row) {
            if (isset($perGender[$row['jenis_kelamin']])) {
                $perGender[$row['jenis_kelamin']] = (int) $row['jumlah'];
            }
        }

        return $perGender;
    }
}