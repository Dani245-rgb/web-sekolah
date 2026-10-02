<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends BaseModel
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id_siswa';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;

    protected $allowedFields = [
        'user_id',
        'nis',
        'nisn',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ortu',
        'no_hp_ortu',
        'email',
        'foto',
        'jurusan_id',
        'deleted_at',
    ];

    protected $validationRules = [
        'nis'           => 'required|max_length[20]|is_unique[siswa.nis,id_siswa,{id_siswa}]',
        'nisn'          => 'required|max_length[20]|is_unique[siswa.nisn,id_siswa,{id_siswa}]',
        'nama'          => 'required|min_length[3]|max_length[100]',
        'jenis_kelamin' => 'required|in_list[L,P]',
        'email'         => 'permit_empty|valid_email',
        'jurusan_id'    => 'permit_empty|is_natural_no_zero',
    ];

    protected $validationMessages = [
        'nis' => [
            'is_unique' => 'NIS ini sudah terdaftar.',
        ],
        'nisn' => [
            'is_unique' => 'NISN ini sudah terdaftar.',
        ],
    ];

    public function getAll()
    {
        return $this->orderBy('nama', 'ASC');
    }

    /**
     * Daftar siswa untuk halaman admin: join jurusan + filter jurusan + pencarian.
     * Mengembalikan model (chainable) supaya bisa langsung ->paginate().
     */
    public function cariAdmin(?string $keyword = null, ?int $jurusanId = null): static
    {
        $builder = $this->getAll()
            ->select('siswa.*, jurusan.kode_jurusan, jurusan.nama_jurusan')
            ->join('jurusan', 'jurusan.id_jurusan = siswa.jurusan_id', 'left');

        if ($jurusanId !== null) {
            $builder->where('siswa.jurusan_id', $jurusanId);
        }

        if ($keyword) {
            $builder->groupStart()
                ->like('siswa.nama', $keyword)
                ->orLike('siswa.nis', $keyword)
                ->orLike('siswa.nisn', $keyword)
                ->orLike('jurusan.nama_jurusan', $keyword)
                ->orLike('jurusan.kode_jurusan', $keyword)
                ->groupEnd();
        }

        return $builder;
    }

    /**
     * Siswa yang ada di tong sampah, terbaru dihapus dulu.
     */
    public function getTerhapus(): array
    {
        return $this->onlyDeleted()->orderBy('deleted_at', 'DESC')->findAll();
    }

    /**
     * Satu siswa dari tong sampah, atau null.
     */
    public function cariTerhapus(int $id): ?array
    {
        return $this->onlyDeleted()->find($id);
    }

    public function nisSudahAda(string $nis): bool
    {
        return $this->where('nis', $nis)->first() !== null;
    }

    public function nisnSudahAda(string $nisn): bool
    {
        return $this->where('nisn', $nisn)->first() !== null;
    }
}