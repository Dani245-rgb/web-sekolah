<?php

namespace App\Services;

use App\Models\JurusanModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Semua aturan bisnis Jurusan ada di sini: normalisasi nama, cek duplikat,
 * slug, transaksi, dan pengelolaan file foto.
 *
 * Konvensi error:
 *  - InvalidArgumentException = kesalahan yang boleh ditampilkan ke user
 *  - Exception lain           = kesalahan sistem (controller yang log + pesan umum)
 */
class JurusanService
{
    private const FOLDER_FOTO = 'jurusan';

    public function __construct(
        private JurusanModel $model,
        private FileUploadService $uploader,
        private SlugService $slugService,
        private BaseConnection $db,
    ) {
    }

    public function daftar(): array
    {
        return $this->model->getSemuaUrut();
    }

    /**
     * Cari jurusan. Null kalau ID bukan angka atau tidak ada.
     */
    public function cari($id): ?array
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->model->find((int) $id);
    }

    public function tambah(array $input, ?UploadedFile $foto = null): void
    {
        $nama = $this->normalisasiNama($input['nama_jurusan'] ?? '');
        $this->pastikanNamaBoleh($nama);

        $fotoBaru = null;
        $this->db->transStart();

        try {
            $fotoBaru = $this->uploader->simpan($foto, self::FOLDER_FOTO);

            $data         = $this->susunData($nama, $input);
            $data['slug'] = $this->slugService->buatUnik($nama, 'jurusan', 'id_jurusan', null, 'jurusan');
            if ($fotoBaru) {
                $data['foto'] = $fotoBaru;
            }

            if ($this->model->insert($data) === false) {
                throw new RuntimeException('Insert jurusan gagal.');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Transaksi database gagal.');
            }
        } catch (Throwable $e) {
            $this->db->transRollback();
            // Foto sudah terupload tapi transaksi gagal: buang supaya tidak jadi sampah
            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            throw $e;
        }
    }

    public function ubah(int $id, array $input, ?UploadedFile $foto = null): void
    {
        $item = $this->cari($id) ?? throw new InvalidArgumentException('Jurusan tidak ditemukan.');

        $nama = $this->normalisasiNama($input['nama_jurusan'] ?? '');
        $this->pastikanNamaBoleh($nama, $id);

        $fotoLama = $item['foto'] ?? null;
        $fotoBaru = null;
        $this->db->transStart();

        try {
            $data = $this->susunData($nama, $input);

            if ($nama !== $item['nama_jurusan']) {
                $data['slug'] = $this->slugService->buatUnik($nama, 'jurusan', 'id_jurusan', $id, 'jurusan');
            }

            $fotoBaru = $this->uploader->simpan($foto, self::FOLDER_FOTO);
            if ($fotoBaru) {
                $data['foto'] = $fotoBaru;
            }

            if ($this->model->update($id, $data) === false) {
                throw new RuntimeException('Update jurusan gagal.');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Transaksi database gagal.');
            }

            // Foto lama baru dihapus SETELAH transaksi sukses
            if ($fotoBaru) {
                $this->uploader->hapus($fotoLama, self::FOLDER_FOTO);
            }
        } catch (Throwable $e) {
            $this->db->transRollback();
            $this->uploader->hapus($fotoBaru, self::FOLDER_FOTO);

            throw $e;
        }
    }

    public function hapus(int $id): void
    {
        $item = $this->cari($id) ?? throw new InvalidArgumentException('Jurusan tidak ditemukan.');

        $this->model->delete($id);
        $this->uploader->hapus($item['foto'] ?? null, self::FOLDER_FOTO);
    }

    /**
     * Buang spasi di pinggir dan ubah spasi ganda jadi satu.
     */
    private function normalisasiNama($nama): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) $nama));
    }

    private function pastikanNamaBoleh(string $nama, ?int $excludeId = null): void
    {
        if ($nama === '') {
            throw new InvalidArgumentException('Nama jurusan tidak boleh kosong/hanya spasi.');
        }

        if ($this->model->namaSudahDipakai($nama, $excludeId)) {
            throw new InvalidArgumentException('Nama jurusan ini sudah terdaftar.');
        }
    }

    private function susunData(string $nama, array $input): array
    {
        $data = [
            'nama_jurusan'  => $nama,
            'deskripsi'     => $input['deskripsi'] ?? null,
            'kompetensi'    => $input['kompetensi'] ?? null,
            'prospek_kerja' => $input['prospek_kerja'] ?? null,
        ];

        // null = field tidak dikirim form, jadi jangan menimpa nilai lama
        if (($input['singkatan'] ?? null) !== null) {
            $data['singkatan'] = $input['singkatan'];
        }

        return $data;
    }
}