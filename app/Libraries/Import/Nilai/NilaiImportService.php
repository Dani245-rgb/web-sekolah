<?php

namespace App\Libraries\Import\Nilai;

use App\Libraries\Import\ImportService;
use App\Libraries\Import\ImportResult;

class NilaiImportService extends ImportService
{
    protected NilaiRepository $repo;
    protected int $idKomponen;

    public function __construct(NilaiRepository $repo, NilaiValidator $validator, int $idKomponen)
    {
        parent::__construct($validator);
        $this->repo = $repo;
        $this->idKomponen = $idKomponen;
    }

    public function jenisImport(): string
    {
        return 'nilai';
    }

    public function confirm(
        ImportResult $result,
        bool $lewatiBarisGagal,
        int $idUser,
        string $namaFile,
        ?int $idReferensi = null,
        bool $timpaKonflik = false
    ): ImportResult {

        if (!$lewatiBarisGagal && $result->totalGagal > 0) {
            throw new \RuntimeException('Import dibatalkan: ada baris gagal dan mode "lewati" tidak aktif.');
        }

        if (!$timpaKonflik && $result->totalKonflik > 0) {
            throw new \RuntimeException(
                "Import dibatalkan: ada {$result->totalKonflik} baris yang datanya sudah diubah pihak lain sejak template di-download. " .
                    'Centang "Tetap timpa data konflik" kalau yakin ingin menimpa, atau download ulang template terbaru.'
            );
        }
        $db = $this->repo->getDb();
        $db->transStart();

        $idImportLog = $this->repo->catatImportLog([
            'jenis_import'   => $this->jenisImport(),
            'id_referensi'   => $idReferensi ?? $this->idKomponen,
            'id_user'        => $idUser,
            'nama_file'      => $namaFile,
            'path_file'      => $namaFile,
            'total_baris'    => count($result->rows),
            'baris_selesai'  => 0,
            'total_sukses'   => $result->totalValid,
            'total_ditimpa'  => $result->totalDitimpa,
            'total_gagal'    => $result->totalGagal,
            'status'         => 'Selesai',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        foreach ($result->rows as $row) {
            if ($row->status === 'gagal') {
                continue; // sudah divalidasi lewatiBarisGagal di atas
            }

            if ($row->status === 'tidak_berubah') {
                continue; // nilai sama persis — skip total, tidak insert/update, tidak audit log
            }

            // status 'valid', 'ditimpa', dan 'konflik' (yang sudah di-approve lewat $timpaKonflik) diproses sama

            $idSiswa = $row->dataBaru['id_siswa'];
            $nilaiBaru = $row->dataBaru['nilai'];

            $hasil = $this->repo->upsert($this->idKomponen, $idSiswa, $nilaiBaru);

            $this->repo->catatAuditLog([
                'id_user'          => $idUser,
                'aksi'             => $hasil['aksi'] === 'insert' ? 'import_insert' : 'import_update',
                'tabel_target'     => 'nilai_siswa',
                'id_record_target' => $hasil['id_nilai'],
                'nilai_lama'       => $hasil['nilai_lama'],
                'nilai_baru'       => $nilaiBaru,
                'sumber'           => 'import',
                'id_import_log'    => $idImportLog,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
        }

        $this->repo->updateImportLog($idImportLog, ['baris_selesai' => count($result->rows)]);

        $db->transComplete();

        $result->idImportLog = $idImportLog;
        return $result;
    }

    // rollback() SUDAH DIPINDAH ke NilaiRollbackService.php — jangan ditulis ulang di sini.
}
