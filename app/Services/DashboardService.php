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

    public function ringkasan(int $idUser, bool $superadmin): array
    {
        return [
            'totalSiswa'        => $this->siswaModel->countAllResults(),
            'totalGuru'         => $this->guruModel->countAllResults(),
            'perGender'         => $this->siswaPerGender(),
            'notifikasiTerbaru' => $this->notifikasiModel->getForUser($idUser, 5),
            'pengumumanTerbaru' => $this->pengumumanModel->getTerbaru(5),
            // Audit log hanya boleh dilihat superadmin, sama seperti halaman Audit Log
            'aktivitasTerbaru'  => $superadmin
                ? $this->auditLogModel->orderBy('created_at', 'DESC')->findAll(5)
                : [],
            'tampilAktivitas'   => $superadmin,
        ];
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