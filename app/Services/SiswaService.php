<?php

namespace App\Services;

use App\Models\SiswaModel;

/**
 * Menyimpan business logic pembuatan data siswa. Dipakai bersama oleh
 * Admin\Siswa::store() (input manual) dan Admin\ImportSiswa (import Excel),
 * supaya logikanya cuma ada di satu tempat.
 */
class SiswaService
{
    public function __construct(
        protected SiswaModel $siswaModel,
    ) {
    }

    /**
     * @param array $data Field siswa yang sudah divalidasi & siap simpan
     *                     (nis, nisn, nama, tempat_lahir, tanggal_lahir,
     *                     jenis_kelamin, agama, alamat, nama_ayah, nama_ibu,
     *                     pekerjaan_ortu, no_hp_ortu, email, jurusan_id, foto)
     * @return array{sukses: bool, pesan: string, id_siswa: ?int}
     */
    public function buatSiswaBaru(array $data): array
    {
        $idSiswa = $this->siswaModel->skipValidation(true)->insert([
            'nis'            => $data['nis'],
            'nisn'           => $data['nisn'],
            'jurusan_id'     => $data['jurusan_id'] ?? null,
            'nama'           => $data['nama'],
            'tempat_lahir'   => $data['tempat_lahir'] ?? null,
            'tanggal_lahir'  => $data['tanggal_lahir'] ?? null,
            'jenis_kelamin'  => $data['jenis_kelamin'],
            'agama'          => $data['agama'] ?? null,
            'alamat'         => $data['alamat'] ?? null,
            'nama_ayah'      => $data['nama_ayah'] ?? null,
            'nama_ibu'       => $data['nama_ibu'] ?? null,
            'pekerjaan_ortu' => $data['pekerjaan_ortu'] ?? null,
            'no_hp_ortu'     => $data['no_hp_ortu'] ?? null,
            'email'          => $data['email'] ?: null,
            'foto'           => $data['foto'] ?? null,
            'status'         => 'Aktif',
        ]);

        if (!$idSiswa) {
            $errors = $this->siswaModel->errors();
            $pesan = $errors ? implode('; ', $errors) : 'Gagal membuat data siswa.';
            return ['sukses' => false, 'pesan' => $pesan, 'id_siswa' => null];
        }

        return ['sukses' => true, 'pesan' => '', 'id_siswa' => $idSiswa];
    }
}