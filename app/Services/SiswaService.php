<?php

namespace App\Services;

use App\Libraries\ImageCompressor;
use App\Models\JurusanModel;
use App\Models\SiswaModel;
use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Business logic data siswa. Dipakai bersama oleh Admin\Siswa (input manual)
 * dan ImportSiswaService (import Excel) lewat buatSiswaBaru().
 *
 * Method aksi mengembalikan array{sukses: bool, pesan: string}.
 */
class SiswaService
{
    private const PER_HALAMAN = 50;

    public function __construct(
        protected SiswaModel $siswaModel,
        protected JurusanModel $jurusanModel,
        protected ImageCompressor $compressor,
    ) {
    }

    // ================= Baca =================

    /**
     * @return array{siswa: array, pager: mixed, jurusanAktif: ?string}
     */
    public function daftar(?string $keyword, $jurusan = null): array
    {
        $jurusanValid = !empty($jurusan) && ctype_digit((string) $jurusan);

        $siswa = $this->siswaModel
            ->cariAdmin($keyword ?: null, $jurusanValid ? (int) $jurusan : null)
            ->paginate(self::PER_HALAMAN);

        return [
            'siswa'        => $siswa,
            'pager'        => $this->siswaModel->pager,
            'jurusanAktif' => $jurusanValid ? (string) $jurusan : null,
        ];
    }

    public function daftarJurusanAktif(): array
    {
        return $this->jurusanModel->getAktifUntukDropdown();
    }

    public function cari($id): ?array
    {
        return $this->siswaModel->find($id);
    }

    public function daftarTerhapus(): array
    {
        return $this->siswaModel->getTerhapus();
    }

    // ================= Tulis =================

    /**
     * Tambah siswa dari form (dengan foto opsional). Foto dibuang lagi kalau simpan gagal.
     */
    public function tambah(array $input, ?UploadedFile $foto = null): array
    {
        $namaFoto = $this->adaFoto($foto) ? $this->simpanFoto($foto) : null;

        $hasil = $this->buatSiswaBaru($input + ['foto' => $namaFoto]);

        if (!$hasil['sukses']) {
            $this->hapusFoto($namaFoto);
        }

        return $hasil;
    }

    /**
     * Dipakai juga oleh import Excel. $data sudah divalidasi dan siap simpan.
     *
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
        ]);

        if (!$idSiswa) {
            $errors = $this->siswaModel->errors();
            $pesan  = $errors ? implode('; ', $errors) : 'Gagal membuat data siswa.';

            return ['sukses' => false, 'pesan' => $pesan, 'id_siswa' => null];
        }

        return ['sukses' => true, 'pesan' => '', 'id_siswa' => $idSiswa];
    }

    public function ubah(int $id, array $input, ?UploadedFile $foto = null): array
    {
        $siswa = $this->cari($id);
        if (!$siswa) {
            return $this->gagal('Data siswa tidak ditemukan.');
        }

        $data = [
            'nis'            => $input['nis'] ?? null,
            'nisn'           => $input['nisn'] ?? null,
            'jurusan_id'     => ($input['jurusan_id'] ?? null) ?: null,
            'nama'           => $input['nama'] ?? null,
            'tempat_lahir'   => $input['tempat_lahir'] ?? null,
            'tanggal_lahir'  => $input['tanggal_lahir'] ?? null,
            'jenis_kelamin'  => $input['jenis_kelamin'] ?? null,
            'agama'          => $input['agama'] ?? null,
            'alamat'         => $input['alamat'] ?? null,
            'nama_ayah'      => $input['nama_ayah'] ?? null,
            'nama_ibu'       => $input['nama_ibu'] ?? null,
            'pekerjaan_ortu' => $input['pekerjaan_ortu'] ?? null,
            'no_hp_ortu'     => $input['no_hp_ortu'] ?? null,
            'email'          => $input['email'] ?? null,
        ];

        $fotoBaru = null;
        if ($this->adaFoto($foto)) {
            $fotoBaru     = $this->simpanFoto($foto);
            $data['foto'] = $fotoBaru;
        }

        $this->siswaModel->skipValidation(true);
        $berhasil = $this->siswaModel->update($id, $data);

        if (!$berhasil) {
            $this->hapusFoto($fotoBaru);

            return $this->gagal('Gagal memperbarui data siswa.');
        }

        // Foto lama baru dibuang SETELAH update sukses
        if ($fotoBaru) {
            $this->hapusFoto($siswa['foto'] ?? null);
        }

        return $this->sukses("Data siswa {$data['nama']} berhasil diperbarui.");
    }

    /**
     * Soft delete: masuk tong sampah. Foto sengaja tidak dihapus
     * sampai siswa dihapus permanen.
     */
    public function hapus(int $id): array
    {
        if (!$this->cari($id)) {
            return $this->gagal('Data siswa tidak ditemukan.');
        }

        if (!$this->siswaModel->delete($id)) {
            return $this->gagal('Gagal menghapus data siswa.');
        }

        return $this->sukses('Data siswa dipindahkan ke tong sampah. Bisa dipulihkan kapan saja dari menu Recycle Bin.');
    }

    public function pulihkan(int $id): array
    {
        $siswa = $this->siswaModel->cariTerhapus($id);
        if (!$siswa) {
            return $this->gagal('Data tidak ditemukan di tong sampah.');
        }

        if (!$this->siswaModel->update($id, ['deleted_at' => null])) {
            return $this->gagal('Gagal memulihkan data siswa.');
        }

        return $this->sukses("Siswa {$siswa['nama']} berhasil dipulihkan.");
    }

    public function hapusPermanen(int $id): array
    {
        $siswa = $this->siswaModel->cariTerhapus($id);
        if (!$siswa) {
            return $this->gagal('Data tidak ditemukan di tong sampah.');
        }

        if (!$this->siswaModel->delete($id, true)) {
            return $this->gagal('Gagal menghapus data siswa secara permanen.');
        }

        $this->hapusFoto($siswa['foto'] ?? null);

        return $this->sukses('Data siswa dihapus permanen.');
    }

    // ================= Helper privat =================

    private function adaFoto(?UploadedFile $foto): bool
    {
        return $foto !== null && $foto->isValid() && !$foto->hasMoved();
    }

    private function simpanFoto(UploadedFile $foto): string
    {
        $nama = $foto->getRandomName();
        $this->compressor->compressAndSave($foto->getTempName(), $this->pathFoto($nama));

        return $nama;
    }

    private function hapusFoto(?string $nama): void
    {
        if (empty($nama)) {
            return;
        }

        $path = $this->pathFoto($nama);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function pathFoto(string $nama): string
    {
        return FCPATH . 'uploads/siswa/' . $nama;
    }

    private function sukses(string $pesan): array
    {
        return ['sukses' => true, 'pesan' => $pesan];
    }

    private function gagal(string $pesan): array
    {
        return ['sukses' => false, 'pesan' => $pesan];
    }
}