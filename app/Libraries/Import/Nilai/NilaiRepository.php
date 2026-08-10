<?php

namespace App\Libraries\Import\Nilai;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class NilaiRepository
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findKomponen(int $idKomponen): ?array
    {
        $row = $this->db->table('komponen_nilai')
            ->where('id_komponen', $idKomponen)
            ->get()->getRowArray();
        return $row ?: null;
    }

    public function findSiswaDiKelas(int $idSiswa, int $idKelas, int $idTahunAjaran): ?array
{
    $row = $this->db->table('siswa s')
        ->select('s.id_siswa, s.nis, s.nama')
        ->join('kelas_siswa ks', 'ks.id_siswa = s.id_siswa')
        ->where('s.id_siswa', $idSiswa)
        ->where('ks.id_kelas', $idKelas)
        ->where('ks.id_tahun_ajaran', $idTahunAjaran)
        ->get()->getRowArray();
    return $row ?: null;
}

    public function findNilaiExisting(int $idKomponen, int $idSiswa): ?array
    {
        $row = $this->db->table('nilai_siswa')
            ->where('id_komponen', $idKomponen)
            ->where('id_siswa', $idSiswa)
            ->get()->getRowArray();
        return $row ?: null;
    }

    public function getRentangNilai(): array
    {
        // dari tabel pengaturan_nilai (sesuai migration CreatePengaturanNilaiTable yang sudah ada)
        $row = $this->db->table('pengaturan_nilai')->get()->getRowArray();
        return [
            'min' => $row['nilai_min'] ?? 0,
            'max' => $row['nilai_maks'] ?? 100,
        ];
    }

    public function upsert(int $idKomponen, int $idSiswa, float $nilai): array
    {
        $existing = $this->findNilaiExisting($idKomponen, $idSiswa);

        if ($existing) {
            $this->db->table('nilai_siswa')
                ->where('id_nilai', $existing['id_nilai'])
                ->update(['nilai' => $nilai, 'updated_at' => date('Y-m-d H:i:s')]);

            return ['aksi' => 'update', 'id_nilai' => $existing['id_nilai'], 'nilai_lama' => $existing['nilai']];
        }

        $this->db->table('nilai_siswa')->insert([
            'id_komponen' => $idKomponen,
            'id_siswa'    => $idSiswa,
            'nilai'       => $nilai,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return ['aksi' => 'insert', 'id_nilai' => $this->db->insertID(), 'nilai_lama' => null];
    }

    public function catatAuditLog(array $data): int
    {
        $this->db->table('audit_log')->insert($data);
        return $this->db->insertID();
    }

    public function catatImportLog(array $data): int
    {
        $this->db->table('import_log')->insert($data);
        return $this->db->insertID();
    }

    public function updateImportLog(int $idImportLog, array $data): void
    {
        $this->db->table('import_log')->where('id_import_log', $idImportLog)->update($data);
    }

    public function ambilAuditLogUntukRollback(int $idImportLog): array
    {
        return $this->db->table('audit_log')
            ->where('id_import_log', $idImportLog)
            ->get()->getResultArray();
    }

    public function cekBerubahSetelah(int $idRecordTarget, string $waktuImport): bool
    {
        $row = $this->db->table('audit_log')
            ->where('tabel_target', 'nilai_siswa')
            ->where('id_record_target', $idRecordTarget)
            ->where('created_at >', $waktuImport)
            ->countAllResults();
        return $row > 0;
    }

    public function revertNilai(int $idNilai, ?float $nilaiLama): void
    {
        if ($nilaiLama === null) {
            $this->db->table('nilai_siswa')->where('id_nilai', $idNilai)->delete();
        } else {
            $this->db->table('nilai_siswa')->where('id_nilai', $idNilai)
                ->update(['nilai' => $nilaiLama, 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }

    public function getDb(): BaseConnection
    {
        return $this->db; // dipakai ImportService buat transStart/transComplete
    }

    public function getRiwayatImport(int $idKomponen, int $limit = 5): array
{
    return $this->db->table('import_log')
        ->where('jenis_import', 'nilai')
        ->where('id_referensi', $idKomponen)
        ->orderBy('created_at', 'DESC')
        ->limit($limit)
        ->get()->getResultArray();
}
}