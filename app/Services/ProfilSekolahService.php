<?php

namespace App\Services;

use App\Models\ProfilKontenModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Aturan bisnis Profil Sekolah: teks (sejarah, visi misi) dan profil kepala sekolah beserta foto.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class ProfilSekolahService
{
    private const FOLDER_FOTO = 'profil'; // public/uploads/profil
    private const JENIS_TEKS  = ['sejarah', 'visi_misi'];

    public function __construct(
        private ProfilKontenModel $model,
        private FileUploadService $uploader,
    ) {
        helper('teks');
    }

    public function jenisTeksValid(string $jenis): bool
    {
        return in_array($jenis, self::JENIS_TEKS, true);
    }

    /**
     * Ambil konten satu jenis. Kalau belum ada barisnya, dibuat kosong dulu.
     */
    public function ambil(string $jenis): array
    {
        return $this->model->getOrCreate($jenis);
    }

    public function simpanTeks(string $jenis, array $input): void
    {
        if (!$this->jenisTeksValid($jenis)) {
            throw new InvalidArgumentException('Jenis tidak valid.');
        }

        $item = $this->ambil($jenis);

        $berhasil = $this->model->update($item['id'], [
            'judul'  => rapikan_teks((string) ($input['judul'] ?? '')),
            'konten' => $input['konten'] ?? null,
        ]);

        if (!$berhasil) {
            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan.'));
        }
    }

    public function simpanKepalaSekolah(array $input, ?UploadedFile $foto = null): void
    {
        $item = $this->ambil('kepala_sekolah');

        $data = [
            'nama'    => rapikan_teks((string) ($input['nama'] ?? '')),
            'jabatan' => rapikan_teks((string) ($input['jabatan'] ?? '')),
            'konten'  => $input['konten'] ?? null,
        ];

        // Foto baru (opsional). Kalau tidak diisi, foto lama dipertahankan.
        $fotoBaru = $this->unggah($foto);

        if ($fotoBaru !== null) {
            $data['foto'] = $fotoBaru;
        }

        if (!$this->model->update($item['id'], $data)) {
            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan.'));
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->uploader->hapus($item['foto'] ?? null, self::FOLDER_FOTO);
        }
    }

    // ================= Helper privat =================

    /**
     * Simpan foto. Null kalau tidak ada file baru. Pesan InvalidArgumentException
     * dari uploader (tipe/ukuran salah) diteruskan apa adanya ke user.
     */
    private function unggah(?UploadedFile $foto): ?string
    {
        try {
            return $this->uploader->simpan($foto, self::FOLDER_FOTO);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload foto Kepala Sekolah: ' . $e->getMessage());

            throw new InvalidArgumentException('Gagal menyimpan foto. Silakan coba lagi.');
        }
    }

    private function pesanModel(string $cadangan): string
    {
        $errors = $this->model->errors();

        return $errors ? implode('; ', $errors) : $cadangan;
    }
}