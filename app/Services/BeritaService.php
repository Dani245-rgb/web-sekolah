<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\BeritaModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;

/**
 * Aturan bisnis Berita: judul, slug, upload gambar, dan pencatatan audit.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class BeritaService
{
    // Gambar berita disimpan di public/assets/images/berita (bukan public/uploads)
    private const FOLDER_GAMBAR = 'assets/images/berita';
    private const MIME_GAMBAR   = ['image/jpeg', 'image/png'];
    private const PER_HALAMAN   = 10;

    public function __construct(
        private BeritaModel $model,
        private AuditLogModel $auditLog,
        private FileUploadService $uploader,
        private SlugService $slugService,
    ) {
        helper('teks');
    }

    /**
     * @return array{berita: array, pager: mixed}
     */
    public function daftar(?string $keyword = null): array
    {
        $berita = $this->model->cariAdmin($keyword ?: null)->paginate(self::PER_HALAMAN);

        return ['berita' => $berita, 'pager' => $this->model->pager];
    }

    public function cari($id): ?array
    {
        return $this->model->find($id);
    }

    public function tambah(array $input, ?UploadedFile $gambar, $idUser, string $namaUser): void
    {
        $judul = $this->rapikanJudul($input['judul'] ?? '');

        $slug     = $this->slugService->buatUnik($judul, 'berita', 'id_berita', null, 'berita');
        $namaFile = $this->unggah($gambar);

        $idBerita = $this->model->insert([
            'judul'           => $judul,
            'slug'            => $slug,
            'kategori'        => $input['kategori'] ?? null,
            'gambar'          => $namaFile,
            'ringkasan'       => $input['ringkasan'] ?? null,
            'konten'          => $input['konten'] ?? null,
            'status'          => $input['status'] ?? null,
            'posisi'          => $input['posisi'] ?? null,
            'tanggal_publish' => ($input['tanggal_publish'] ?? null) ?: date('Y-m-d'),
            'id_user'         => $idUser,
        ]);

        if (!$idBerita) {
            // Insert gagal: buang gambar yang sudah terlanjur diupload
            $this->uploader->hapus($namaFile, self::FOLDER_GAMBAR);

            throw new InvalidArgumentException($this->pesanModel('Gagal menyimpan berita.'));
        }

        $this->catatAudit($idUser, $namaUser, 'Tambah Berita', $judul);
    }

    public function ubah(int $id, array $input, ?UploadedFile $gambar, $idUser, string $namaUser): void
    {
        $berita = $this->cari($id) ?? throw new InvalidArgumentException('Berita tidak ditemukan.');

        $judul = $this->rapikanJudul($input['judul'] ?? '');

        $slug = $judul !== $berita['judul']
            ? $this->slugService->buatUnik($judul, 'berita', 'id_berita', $id, 'berita')
            : $berita['slug'];

        $gambarBaru = $this->unggah($gambar);

        $berhasil = $this->model->update($id, [
            'judul'           => $judul,
            'slug'            => $slug,
            'kategori'        => $input['kategori'] ?? null,
            'gambar'          => $gambarBaru ?? $berita['gambar'],
            'ringkasan'       => $input['ringkasan'] ?? null,
            'konten'          => $input['konten'] ?? null,
            'status'          => $input['status'] ?? null,
            'posisi'          => $input['posisi'] ?? null,
            'tanggal_publish' => ($input['tanggal_publish'] ?? null) ?: $berita['tanggal_publish'],
        ]);

        if (!$berhasil) {
            // Update gagal: buang gambar baru, gambar lama tetap aman
            $this->uploader->hapus($gambarBaru, self::FOLDER_GAMBAR);

            throw new InvalidArgumentException($this->pesanModel('Gagal memperbarui berita.'));
        }

        // Gambar lama baru dihapus SETELAH update berhasil
        if ($gambarBaru) {
            $this->uploader->hapus($berita['gambar'] ?? null, self::FOLDER_GAMBAR);
        }

        $this->catatAudit($idUser, $namaUser, 'Edit Berita', $judul);
    }

    public function hapus(int $id, $idUser, string $namaUser): void
    {
        $berita = $this->cari($id) ?? throw new InvalidArgumentException('Berita tidak ditemukan.');

        $this->model->delete($id);
        $this->uploader->hapus($berita['gambar'] ?? null, self::FOLDER_GAMBAR);

        $this->catatAudit($idUser, $namaUser, 'Hapus Berita', $berita['judul']);
    }

    // ================= Helper privat =================

    private function rapikanJudul($judul): string
    {
        $judul = rapikan_teks((string) $judul);

        if (mb_strlen($judul) < 5) {
            throw new InvalidArgumentException('Judul minimal 5 karakter.');
        }

        return $judul;
    }

    /**
     * Simpan gambar. Null kalau tidak ada file baru. Pesan InvalidArgumentException
     * dari uploader (tipe/ukuran salah) diteruskan apa adanya ke user.
     */
    private function unggah(?UploadedFile $gambar): ?string
    {
        try {
            return $this->uploader->simpan($gambar, self::FOLDER_GAMBAR, self::MIME_GAMBAR);
        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            log_message('error', 'Gagal upload gambar Berita: ' . $e->getMessage());

            throw new InvalidArgumentException('Gagal menyimpan gambar. Periksa permission folder.');
        }
    }

    private function pesanModel(string $cadangan): string
    {
        $errors = $this->model->errors();

        return $errors ? implode('; ', $errors) : $cadangan;
    }

    private function catatAudit($idUser, string $namaUser, string $aksi, string $judul): void
    {
        $this->auditLog->catat($idUser, $namaUser, $aksi, "Judul:{$judul}");
    }
}