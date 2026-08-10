<?php

namespace App\Libraries\Import\Nilai;

use App\Libraries\Import\ImportRow;
use App\Libraries\Import\ImportValidatorInterface;

class NilaiValidator implements ImportValidatorInterface
{
    protected NilaiRepository $repo;
    protected int $idKomponen;
    protected int $idKelas;
    protected int $idTahunAjaran;

    public function __construct(NilaiRepository $repo, int $idKomponen, int $idKelas, int $idTahunAjaran)
    {
        $this->repo = $repo;
        $this->idKomponen = $idKomponen;
        $this->idKelas = $idKelas;
        $this->idTahunAjaran = $idTahunAjaran;
    }

    public function validateRow(int $nomorBaris, array $rawRow): ImportRow
    {
        $row = new ImportRow($nomorBaris, $rawRow);

        // Kolom Excel: A=ID Siswa, B=NIS, C=Nama, D=Nilai, E=Snapshot (tersembunyi)
        $idSiswa = (int) trim((string)($rawRow['A'] ?? ''));
        $nilaiRaw = trim((string)($rawRow['D'] ?? ''));
        $snapshotRaw = trim((string)($rawRow['E'] ?? ''));

        // 1. Cek id_siswa valid & ada di kelas ini (di tahun ajaran ini)
        $siswa = $this->repo->findSiswaDiKelas($idSiswa, $this->idKelas, $this->idTahunAjaran);
        if (!$siswa) {
            $row->status = 'gagal';
            $row->alasanGagal = "ID Siswa {$idSiswa} tidak ditemukan di kelas ini";
            return $row;
        }

        // 2. Cek nilai numerik
        if ($nilaiRaw === '' || !is_numeric($nilaiRaw)) {
            $row->status = 'gagal';
            $row->alasanGagal = "Nilai '{$nilaiRaw}' bukan angka yang valid";
            return $row;
        }
        $nilai = (float) $nilaiRaw;

        // 3. Cek rentang nilai dari konfigurasi sekolah
        $rentang = $this->repo->getRentangNilai();
        if ($nilai < $rentang['min'] || $nilai > $rentang['max']) {
            $row->status = 'gagal';
            $row->alasanGagal = "Nilai {$nilai} di luar rentang {$rentang['min']}-{$rentang['max']}";
            return $row;
        }

        $existing = $this->repo->findNilaiExisting($this->idKomponen, $idSiswa);
        $row->dataBaru = ['id_siswa' => $idSiswa, 'nis' => $siswa['nis'], 'nama' => $siswa['nama'], 'nilai' => $nilai];

        // 4. Deteksi konflik: bandingkan snapshot (nilai saat template di-download)
        //    dengan nilai TERKINI di database. Kalau beda, berarti ada perubahan
        //    dari pihak lain sejak guru men-download file ini.
        $snapshotDiketahui = $snapshotRaw !== ''; // kosong total = template lama sebelum fitur ini ada
        $nilaiSnapshot = null;
        if ($snapshotDiketahui) {
            $nilaiSnapshot = ($snapshotRaw === 'KOSONG') ? null : (float) $snapshotRaw;
        }

        $nilaiSaatIni = $existing ? (float) $existing['nilai'] : null;

        if ($snapshotDiketahui && !$this->nilaiSama($nilaiSnapshot, $nilaiSaatIni)) {
            $row->status = 'konflik';
            $row->dataLama = ['nilai' => $existing['nilai'] ?? null];
            $row->nilaiSnapshot = $nilaiSnapshot;
            return $row;
        }

        // 5. Tidak ada konflik — lanjut logic seperti biasa
        if ($existing) {
            if ($this->nilaiSama((float) $existing['nilai'], $nilai)) {
                $row->status = 'tidak_berubah';
                $row->dataLama = ['nilai' => $existing['nilai']];
            } else {
                $row->status = 'ditimpa';
                $row->dataLama = ['nilai' => $existing['nilai']];
            }
        } else {
            $row->status = 'valid';
        }

        return $row;
    }

    /**
     * Bandingkan 2 nilai float (atau null) dengan toleransi pembulatan.
     */
    private function nilaiSama(?float $a, ?float $b): bool
    {
        if ($a === null && $b === null) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }
        return abs($a - $b) < 0.0001;
    }
}