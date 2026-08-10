<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalModel extends Model
{
    protected $table         = 'jadwal';
    protected $primaryKey    = 'id_jadwal';
    protected $allowedFields = ['id_semester', 'id_kelas', 'id_mapel', 'id_guru', 'id_ruangan', 'hari', 'jam_mulai', 'jam_selesai', 'status'];
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    public function getAllWithRelasi(int $idSemester, array $filter = [])
    {
        $builder = $this->select('jadwal.*, kelas.nama_kelas, jurusan.nama_jurusan, mapel.nama_mapel, guru.nama as nama_guru, ruangan.nama_ruangan')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('jurusan', 'jurusan.id_jurusan = kelas.id_jurusan', 'left')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->join('guru', 'guru.id_guru = jadwal.id_guru')
            ->join('ruangan', 'ruangan.id_ruangan = jadwal.id_ruangan')
            ->where('jadwal.id_semester', $idSemester);

        if (!empty($filter['id_jurusan'])) {
            $builder->where('kelas.id_jurusan', $filter['id_jurusan']);
        }
        if (!empty($filter['id_kelas'])) {
            $builder->where('jadwal.id_kelas', $filter['id_kelas']);
        }
        if (!empty($filter['hari'])) {
            $builder->where('jadwal.hari', $filter['hari']);
        }
        if (!empty($filter['keyword'])) {
            $builder->groupStart()
                ->like('mapel.nama_mapel', $filter['keyword'])
                ->orLike('guru.nama', $filter['keyword'])
                ->orLike('kelas.nama_kelas', $filter['keyword'])
                ->groupEnd();
        }

        return $builder
            ->orderBy("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')", '', false)
            ->orderBy('jam_mulai', 'ASC')
            ->findAll();
    }

    /**
     * Cek bentrok Guru, Kelas, atau Ruangan di slot hari+jam yang sama.
     * Dipakai bareng-bareng di: form manual (store/update) DAN import (Excel/PDF).
     * Return array kosong kalau aman, atau array pesan error kalau bentrok.
     */
    public function cekBentrok(array $data, ?int $kecualiId = null): array
    {
        $errors = [];

        $overlap = function ($builder) use ($data) {
            // Anggap bentrok kalau ada irisan waktu, bukan cuma jam_mulai yang sama persis
            return $builder->where('hari', $data['hari'])
                ->where('id_semester', $data['id_semester'])
                ->where('jam_mulai <', $data['jam_selesai'])
                ->where('jam_selesai >', $data['jam_mulai']);
        };

        $cekKelas = $overlap($this->where('id_kelas', $data['id_kelas']));
        if ($kecualiId) $cekKelas->where('id_jadwal !=', $kecualiId);
        if ($cekKelas->countAllResults() > 0) {
            $errors[] = "Kelas sudah punya jadwal lain di {$data['hari']} jam {$data['jam_mulai']}-{$data['jam_selesai']}.";
        }

        $cekGuru = $overlap($this->where('id_guru', $data['id_guru']));
        if ($kecualiId) $cekGuru->where('id_jadwal !=', $kecualiId);
        if ($cekGuru->countAllResults() > 0) {
            $errors[] = "Guru sudah mengajar di kelas lain pada {$data['hari']} jam {$data['jam_mulai']}-{$data['jam_selesai']}.";
        }

        $cekRuangan = $overlap($this->where('id_ruangan', $data['id_ruangan']));
        if ($kecualiId) $cekRuangan->where('id_jadwal !=', $kecualiId);
        if ($cekRuangan->countAllResults() > 0) {
            $errors[] = "Ruangan sudah dipakai kelas lain pada {$data['hari']} jam {$data['jam_mulai']}-{$data['jam_selesai']}.";
        }

        return $errors;
    }
}