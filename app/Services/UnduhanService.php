<?php

namespace App\Services;

use App\Models\UnduhanModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Aturan bisnis Unduhan: ekstensi/MIME yang diizinkan, ukuran maksimal,
 * transaksi, dan pengelolaan file.
 *
 * InvalidArgumentException = pesan aman ditampilkan ke user.
 * Exception lain           = kesalahan sistem (controller yang log + pesan umum).
 */
class UnduhanService
{
    private const FOLDER_FILE = 'unduhan';
    private const MAKS_BYTES  = 10 * 1024 * 1024; // 10MB

    /**
     * Ekstensi yang boleh diupload => tipe isi file (MIME) yang sah untuk ekstensi itu.
     * File OOXML (docx/pptx/xlsx) kadang terdeteksi sebagai application/zip, jadi ikut diizinkan.
     * File Office lama (doc/ppt/xls) kadang terdeteksi sebagai application/CDFV2.
     */
    private const TIPE_FILE = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword', 'application/CDFV2'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'ppt'  => ['application/vnd.ms-powerpoint', 'application/CDFV2'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        'xls'  => ['application/vnd.ms-excel', 'application/CDFV2'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'zip'  => ['application/zip'],
    ];

    public function __construct(
        private UnduhanModel $model,
        private FileUploadService $uploader,
        private BaseConnection $db,
    ) {
        helper('teks');
    }

    public function daftar(?string $kategori = null): array
    {
        return $this->model->getDaftarAdmin($kategori ?: null);
    }

    public function daftarKategori(): array
    {
        return $this->model->getKategoriList();
    }

    public function cari($id): ?array
    {
        if (!is_numeric($id)) {
            return null;
        }

        return $this->model->find((int) $id);
    }

    public function tambah(array $input, ?UploadedFile $file): void
    {
        $judul = rapikan_teks($input['judul'] ?? '');

        if ($judul === '') {
            throw new InvalidArgumentException('Judul tidak boleh kosong/hanya spasi.');
        }

        $fileBaru = null;
        $this->db->transStart();

        try {
            $info     = $this->simpanFile($file);
            $fileBaru = $info['nama_file'];

            $data = [
                'kategori'       => rapikan_teks($input['kategori'] ?? ''),
                'judul'          => $judul,
                'deskripsi'      => $input['deskripsi'] ?? null,
                'file'           => $info['nama_file'],
                'nama_file_asli' => $info['nama_asli'],
                'ukuran_file'    => $info['ukuran'],
                'ekstensi'       => $info['ekstensi'],
                'status'         => ($input['status'] ?? null) ?: 'Draft',
            ];

            if ($this->model->insert($data) === false) {
                throw new RuntimeException('Insert unduhan gagal.');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Transaksi database gagal.');
            }
        } catch (Throwable $e) {
            $this->db->transRollback();
            $this->uploader->hapus($fileBaru, self::FOLDER_FILE);

            throw $e;
        }
    }

    public function ubah(int $id, array $input, ?UploadedFile $file = null): void
    {
        $item = $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $judul = rapikan_teks($input['judul'] ?? '');

        if ($judul === '') {
            throw new InvalidArgumentException('Judul tidak boleh kosong/hanya spasi.');
        }

        $adaFileBaru = $file !== null && $file->isValid() && !$file->hasMoved();
        $fileLama    = $item['file'] ?? null;
        $fileBaru    = null;

        $this->db->transStart();

        try {
            $data = [
                'kategori'  => rapikan_teks($input['kategori'] ?? ''),
                'judul'     => $judul,
                'deskripsi' => $input['deskripsi'] ?? null,
                'status'    => ($input['status'] ?? null) ?: 'Draft',
            ];

            if ($adaFileBaru) {
                $info     = $this->simpanFile($file);
                $fileBaru = $info['nama_file'];

                $data['file']           = $info['nama_file'];
                $data['nama_file_asli'] = $info['nama_asli'];
                $data['ukuran_file']    = $info['ukuran'];
                $data['ekstensi']       = $info['ekstensi'];
            }

            if ($this->model->update($id, $data) === false) {
                throw new RuntimeException('Update unduhan gagal.');
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new RuntimeException('Transaksi database gagal.');
            }

            // File lama baru dihapus SETELAH transaksi sukses
            if ($fileBaru) {
                $this->uploader->hapus($fileLama, self::FOLDER_FILE);
            }
        } catch (Throwable $e) {
            $this->db->transRollback();
            $this->uploader->hapus($fileBaru, self::FOLDER_FILE);

            throw $e;
        }
    }

    public function hapus(int $id): void
    {
        $item = $this->cari($id) ?? throw new InvalidArgumentException('Data tidak ditemukan.');

        $this->model->delete($id);
        $this->uploader->hapus($item['file'] ?? null, self::FOLDER_FILE);
    }

    /**
     * Ekstensi harus ada di daftar TIPE_FILE, dan isi file asli harus cocok
     * dengan ekstensi itu (dicek di FileUploadService).
     *
     * @return array{nama_file: string, nama_asli: string, ukuran: int, ekstensi: string}
     */
    private function simpanFile(?UploadedFile $file): array
    {
        if ($file === null || !$file->isValid() || $file->hasMoved()) {
            throw new InvalidArgumentException('File tidak valid.');
        }

        $ekstensi = strtolower($file->getClientExtension());

        if (!isset(self::TIPE_FILE[$ekstensi])) {
            throw new InvalidArgumentException('Ekstensi file tidak diizinkan.');
        }

        // Ambil info dulu, sebelum file dipindah
        $namaAsli = $file->getClientName();
        $ukuran   = (int) $file->getSize();

        $namaFile = $this->uploader->simpan($file, self::FOLDER_FILE, self::TIPE_FILE[$ekstensi], self::MAKS_BYTES);

        if ($namaFile === null) {
            throw new InvalidArgumentException('File tidak valid.');
        }

        return [
            'nama_file' => $namaFile,
            'nama_asli' => $namaAsli,
            'ukuran'    => $ukuran,
            'ekstensi'  => $ekstensi,
        ];
    }
}