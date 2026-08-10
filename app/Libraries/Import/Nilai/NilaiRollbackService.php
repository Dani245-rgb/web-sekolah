<?php

namespace App\Libraries\Import\Nilai;

class NilaiRollbackService
{
    protected NilaiRepository $repo;

    public function __construct(NilaiRepository $repo)
    {
        $this->repo = $repo;
    }

    public function rollback(int $idImportLog, int $idUser): array
    {
        $db = $this->repo->getDb();
        $auditRows = $this->repo->ambilAuditLogUntukRollback($idImportLog);

        if (empty($auditRows)) {
            throw new \RuntimeException('Import log tidak ditemukan atau tidak ada baris untuk di-rollback');
        }

        $laporan = ['berhasil' => 0, 'dilewati' => []];

        $db->transStart();

        foreach ($auditRows as $log) {
            if (!in_array($log['aksi'], ['import_insert', 'import_update'], true)) {
                continue; // baris gagal/skip gak perlu di-revert
            }

            $sudahBerubah = $this->repo->cekBerubahSetelah((int)$log['id_record_target'], $log['created_at']);
            if ($sudahBerubah) {
                $laporan['dilewati'][] = $log['id_record_target'];
                continue;
            }

            $nilaiLama = $log['nilai_lama'] !== null ? (float) $log['nilai_lama'] : null;
            $this->repo->revertNilai((int)$log['id_record_target'], $nilaiLama);

            $this->repo->catatAuditLog([
                'id_user'          => $idUser,
                'aksi'             => 'rollback',
                'tabel_target'     => 'nilai_siswa',
                'id_record_target' => $log['id_record_target'],
                'nilai_lama'       => $log['nilai_baru'],
                'nilai_baru'       => $nilaiLama,
                'sumber'           => 'import',
                'id_import_log'    => $idImportLog,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            $laporan['berhasil']++;
        }

        $this->repo->updateImportLog($idImportLog, ['status' => 'di-rollback']);

        $db->transComplete();

        return $laporan;
    }
}