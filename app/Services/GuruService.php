<?php

namespace App\Services;

use App\Models\GuruModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Aturan bisnis Guru: normalisasi input, foto (dikompres), dan penonaktifan.
 * Guru tidak pernah dihapus, hanya diubah statusnya jadi Nonaktif.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class GuruService
{
    private const FOLDER      = 'guru'; // public/uploads/guru
    private const MIME_FOTO   = ['image/jpeg', 'image/png'];
    private const PER_HALAMAN = 5;

    public function __construct(
        private GuruModel $model,
        private FileUploadService $uploader,
    ) {
        helper('teks');
    }

    /**
     * @return array{guru: array, pager: mixed, keyword: ?string}
     */
    public function daftar($cari = null): array
    {
        $keyword = is_string($cari) && trim($cari) !== '' ? trim($cari) : null;

        $guru = $this->model->cari($keyword)->paginate(self::PER_HALAMAN, 'guru');

        return ['guru' => $guru, 'pager' => $this->model->pager, 'keyword' => $keyword];
    }

    public function cari($id): ?array
    {
        return $this->model->findWithUser($id);
    }

    /**
     * Rapikan input form. Kolom opsional yang kosong (nuptk, tanggal_lahir, email)
     * jadi NULL. Status hanya ikut kalau dikirim form (halaman edit).
     * Hasilnya dipakai untuk validasi di controller, lalu dikirim ke tambah()/ubah().
     */
    public function rapikanInput(array $raw): array
    {
        $data = [
            'nip'           => rapikan_teks((string) ($raw['nip'] ?? '')),
            'nuptk'         => $this->atauNull(rapikan_teks((string) ($raw['nuptk'] ?? ''))),
            'nama'          => rapikan_teks((string) ($raw['nama'] ?? '')),
            'tempat_lahir'  => rapikan_teks((string) ($raw['tempat_lahir'] ?? '')),
            'tanggal_lahir' => $this->atauNull((string) ($raw['tanggal_lahir'] ?? '')),
            'jenis_kelamin' => $raw['jenis_kelamin'] ?? null,
            'jabatan'       => rapikan_teks((string) ($raw['jabatan'] ?? '')),
            'no_hp'         => rapikan_teks((string) ($raw['no_hp'] ?? '')),
            'email'         => $this->atauNull(rapikan_teks((string) ($raw['email'] ?? ''))),
        ];

        if (($raw['status'] ?? null) !== null) {
            $data['status'] = (string) $raw['status'];
        }

        return $data;
    }

    /**
     * @param array $data Hasil rapikanInput() yang sudah lolos validasi
     */
    public function tambah(array $data, ?UploadedFile $foto = null): void
    {
        $namaFoto = $this->unggah($foto);

        $data['foto']   = $namaFoto;
        $data['status'] = 'Aktif';

        $this->model->skipValidation(true);
        $idGuru = $this->model->insert($data);

        if (!$idGuru) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->uploader->hapus($namaFoto, self::FOLDER);

            $errors = $this->model->errors();

            throw new InvalidArgumentException($errors ? implode('; ', $errors) : 'Gagal menyimpan data guru.');
        }
    }

    /**
     * @param array $data Hasil rapikanInput() yang sudah lolos validasi
     */
    public function ubah(int $id, array $data, ?UploadedFile $foto = null): void
    {
        $guru = $this->cari($id) ?? throw new InvalidArgumentException('Data guru tidak ditemukan.');

        // Foto baru (opsional). Kalau tidak diisi, foto lama dipertahankan.
        $fotoBaru = $this->unggah($foto);

        if ($fotoBaru !== null) {
            $data['foto'] = $fotoBaru;
        }

        $this->model->skipValidation(true);

        if (!$this->model->update($id, $data)) {
            $this->uploader->hapus($fotoBaru, self::FOLDER);

            throw new InvalidArgumentException('Gagal memperbarui data guru.');
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->uploader->hapus($guru['foto'] ?? null, self::FOLDER);
        }
    }

    public function nonaktifkan(int $id): void
    {
        $this->cari($id) ?? throw new InvalidArgumentException('Data guru tidak ditemukan.');

        $this->model->skipValidation(true);

        if (!$this->model->update($id, ['status' => 'Nonaktif'])) {
            throw new InvalidArgumentException('Gagal menonaktifkan guru.');
        }
    }

    // ================= Helper privat =================

    /**
     * Simpan foto (dikompres). Null kalau tidak ada file baru. Pesan InvalidArgumentException
     * dari uploader (tipe/ukuran salah) diteruskan apa adanya ke user.
     */
    private function unggah(?UploadedFile $foto): ?string
    {
        try {
            return $this->uploader->simpanKompres($foto, self::FOLDER, self::MIME_FOTO);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload foto Guru: ' . $e->getMessage());

            throw new InvalidArgumentException('Gagal menyimpan foto. Silakan coba lagi.');
        }
    }

    private function atauNull(string $nilai): ?string
    {
        return $nilai !== '' ? $nilai : null;
    }
}