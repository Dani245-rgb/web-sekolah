<?php

namespace App\Services;

use App\Models\AnggotaOrganisasiModel;
use App\Models\OrganisasiModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Aturan bisnis Anggota Organisasi: kepemilikan anggota terhadap organisasi,
 * upload foto (opsional), dan urutan hapus file.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class AnggotaOrganisasiService
{
    private const FOLDER = 'organisasi'; // public/uploads/organisasi

    public function __construct(
        private AnggotaOrganisasiModel $model,
        private OrganisasiModel $organisasiModel,
        private FileUploadService $uploader,
    ) {
        helper('teks');
    }

    public function cariOrganisasi($idOrganisasi): ?array
    {
        return $this->organisasiModel->find($idOrganisasi);
    }

    public function daftar(int $idOrganisasi): array
    {
        return $this->model->getByOrganisasi($idOrganisasi);
    }

    /**
     * Cari anggota, sekaligus pastikan dia memang milik organisasi di URL.
     */
    public function cariAnggota(int $idOrganisasi, $id): ?array
    {
        $item = $this->model->find($id);

        if (!$item || (int) $item['id_organisasi'] !== $idOrganisasi) {
            return null;
        }

        return $item;
    }

    public function tambah(int $idOrganisasi, array $input, ?UploadedFile $foto = null): void
    {
        $this->cariOrganisasi($idOrganisasi) ?? throw new InvalidArgumentException('Organisasi tidak ditemukan.');

        // Foto opsional. Kalau tidak diupload, hasilnya null.
        $namaFoto = $this->unggah($foto);

        $berhasil = $this->model->insert([
            'id_organisasi' => $idOrganisasi,
            'nama'          => rapikan_teks((string) ($input['nama'] ?? '')),
            'jabatan'       => rapikan_teks((string) ($input['jabatan'] ?? '')),
            'foto'          => $namaFoto,
            'urutan'        => (int) ($input['urutan'] ?? 0),
        ]);

        if (!$berhasil) {
            $this->uploader->hapus($namaFoto, self::FOLDER);

            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan anggota.'));
        }
    }

    public function ubah(int $idOrganisasi, int $id, array $input, ?UploadedFile $foto = null): void
    {
        $item = $this->cariAnggota($idOrganisasi, $id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $data = [
            'nama'    => rapikan_teks((string) ($input['nama'] ?? '')),
            'jabatan' => rapikan_teks((string) ($input['jabatan'] ?? '')),
            'urutan'  => (int) ($input['urutan'] ?? 0),
        ];

        // Foto baru (opsional). Kalau tidak diisi, foto lama dipertahankan.
        $fotoBaru = $this->unggah($foto);

        if ($fotoBaru !== null) {
            $data['foto'] = $fotoBaru;
        }

        if (!$this->model->update($id, $data)) {
            $this->uploader->hapus($fotoBaru, self::FOLDER);

            throw new InvalidArgumentException($this->pesanModel('Gagal memperbarui anggota.'));
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->uploader->hapus($item['foto'] ?? null, self::FOLDER);
        }
    }

    /**
     * Hapus anggota. Kalau anggota tidak ada atau bukan milik organisasi itu,
     * tidak melakukan apa pun (perilaku lama dipertahankan).
     */
    public function hapus(int $idOrganisasi, int $id): void
    {
        $item = $this->cariAnggota($idOrganisasi, $id);

        if (!$item) {
            return;
        }

        $this->model->delete($id);

        // File dihapus SETELAH record DB terhapus
        $this->uploader->hapus($item['foto'] ?? null, self::FOLDER);
    }

    // ================= Helper privat =================

    /**
     * Simpan foto. Null kalau tidak ada file baru. Pesan InvalidArgumentException
     * dari uploader (tipe/ukuran salah) diteruskan apa adanya ke user.
     */
    private function unggah(?UploadedFile $foto): ?string
    {
        try {
            return $this->uploader->simpan($foto, self::FOLDER);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload foto Anggota Organisasi: ' . $e->getMessage());

            throw new InvalidArgumentException('Gagal menyimpan foto. Silakan coba lagi.');
        }
    }

    private function pesanModel(string $cadangan): string
    {
        $errors = $this->model->errors();

        return $errors ? implode('; ', $errors) : $cadangan;
    }
}