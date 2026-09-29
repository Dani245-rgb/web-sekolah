<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Libraries\ImageCompressor;
use Config\Database;

class Siswa extends BaseController
{
    protected SiswaModel $siswaModel;
    protected \App\Services\SiswaService $siswaService;

    public function __construct()
    {
        $this->siswaModel   = new SiswaModel();
        $this->siswaService = service('siswaService');
    }

    public function index()
    {
        $keyword = $this->request->getGet('cari');

        $builder = $this->siswaModel->getAll()
            ->select('siswa.*, jurusan.kode_jurusan, jurusan.nama_jurusan')
            ->join('jurusan', 'jurusan.id_jurusan = siswa.jurusan_id', 'left');

        $jurusanId = $this->request->getGet('jurusan');
        if (!empty($jurusanId) && ctype_digit((string) $jurusanId)) {
            $builder->where('siswa.jurusan_id', (int) $jurusanId);
        } else {
            $jurusanId = null;
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

        $data['siswa']   = $builder->paginate(50);
        $data['pager']   = $this->siswaModel->pager;
        $data['keyword']      = $keyword;
        $data['jurusanList']  = $this->getJurusanList();
        $data['jurusanAktif'] = $jurusanId;

        return view('admin/siswa/index', $data);
    }

    // Daftar jurusan aktif untuk dropdown di form tambah/edit
    protected function getJurusanList(): array
    {
        return Database::connect()->table('jurusan')
            ->select('id_jurusan, kode_jurusan, nama_jurusan')
            ->where('deleted_at', null)
            ->where('status', 'Aktif')
            ->orderBy('nama_jurusan', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function create()
    {
        $data['jurusanList'] = $this->getJurusanList();
        return view('admin/siswa/create', $data);
    }

    public function store()
    {
        $rules = [
            'nis'            => 'required|max_length[20]|is_unique[siswa.nis]',
            'nisn'           => 'required|max_length[20]|is_unique[siswa.nisn]',
            'nama'           => 'required|min_length[3]|max_length[100]',
            'tanggal_lahir'  => 'required|valid_date',
            'jurusan_id'     => 'permit_empty|is_not_unique[jurusan.id_jurusan]',
            'jenis_kelamin'  => 'required|in_list[L,P]',
            'email'          => 'permit_empty|valid_email',
            'foto'           => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fotoName = null;
        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $fotoName = $fotoFile->getRandomName();
            $compressor = new ImageCompressor();
            $compressor->compressAndSave($fotoFile->getTempName(), FCPATH . 'uploads/siswa/' . $fotoName);
        }

        $hasil = $this->siswaService->buatSiswaBaru([
            'nis'            => $this->request->getPost('nis'),
            'nisn'           => $this->request->getPost('nisn'),
            'jurusan_id'     => $this->request->getPost('jurusan_id') ?: null,
            'nama'           => $this->request->getPost('nama'),
            'tempat_lahir'   => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'  => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin'  => $this->request->getPost('jenis_kelamin'),
            'agama'          => $this->request->getPost('agama'),
            'alamat'         => $this->request->getPost('alamat'),
            'nama_ayah'      => $this->request->getPost('nama_ayah'),
            'nama_ibu'       => $this->request->getPost('nama_ibu'),
            'pekerjaan_ortu' => $this->request->getPost('pekerjaan_ortu'),
            'no_hp_ortu'     => $this->request->getPost('no_hp_ortu'),
            'email'          => $this->request->getPost('email'),
            'foto'           => $fotoName,
        ]);

        if (!$hasil['sukses']) {
            return redirect()->back()->withInput()->with('errors', ['db' => $hasil['pesan']]);
        }

        return redirect()->to('/admin/siswa')
            ->with('success', "Siswa {$this->request->getPost('nama')} berhasil ditambahkan.");
    }

    public function edit($id_siswa)
    {
        $data['siswaData'] = $this->siswaModel->find($id_siswa);

        if (!$data['siswaData']) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

        $data['jurusanList'] = $this->getJurusanList();

        return view('admin/siswa/edit', $data);
    }

    public function update($id_siswa)
    {
        $siswaData = $this->siswaModel->find($id_siswa);
        if (!$siswaData) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

        $rules = [
            'nis'           => "required|max_length[20]|is_unique[siswa.nis,id_siswa,{$id_siswa}]",
            'nisn'          => "required|max_length[20]|is_unique[siswa.nisn,id_siswa,{$id_siswa}]",
            'jurusan_id'    => 'permit_empty|is_not_unique[jurusan.id_jurusan]',
            'nama'          => 'required|min_length[3]|max_length[100]',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'email'         => 'permit_empty|valid_email',
            'foto'          => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataUpdate = [
            'nis'            => $this->request->getPost('nis'),
            'nisn'           => $this->request->getPost('nisn'),
            'jurusan_id'     => $this->request->getPost('jurusan_id') ?: null,
            'nama'           => $this->request->getPost('nama'),
            'tempat_lahir'   => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'  => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin'  => $this->request->getPost('jenis_kelamin'),
            'agama'          => $this->request->getPost('agama'),
            'alamat'         => $this->request->getPost('alamat'),
            'nama_ayah'      => $this->request->getPost('nama_ayah'),
            'nama_ibu'       => $this->request->getPost('nama_ibu'),
            'pekerjaan_ortu' => $this->request->getPost('pekerjaan_ortu'),
            'no_hp_ortu'     => $this->request->getPost('no_hp_ortu'),
            'email'          => $this->request->getPost('email'),
        ];

        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            if (!empty($siswaData['foto']) && file_exists(FCPATH . 'uploads/siswa/' . $siswaData['foto'])) {
                unlink(FCPATH . 'uploads/siswa/' . $siswaData['foto']);
            }
            $fotoName = $fotoFile->getRandomName();
            $compressor = new ImageCompressor();
            $compressor->compressAndSave($fotoFile->getTempName(), FCPATH . 'uploads/siswa/' . $fotoName);
            $dataUpdate['foto'] = $fotoName;
        }

        $berhasil = $this->siswaModel->skipValidation(true)->update($id_siswa, $dataUpdate);

        if (!$berhasil) {
            return redirect()->back()->withInput()
                ->with('errors', ['update' => 'Gagal memperbarui data siswa.']);
        }

        return redirect()->to('/admin/siswa')->with('success', "Data siswa {$dataUpdate['nama']} berhasil diperbarui.");
    }

    public function delete($id_siswa)
    {
        $siswaData = $this->siswaModel->find($id_siswa);
        if (!$siswaData) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

        // Soft delete: data siswa dipindah ke "tong sampah", bukan dihapus permanen.
        // Foto TIDAK dihapus dari server, disimpan sampai nanti dihapus permanen dari Recycle Bin.
        $berhasil = $this->siswaModel->delete($id_siswa);

        if (!$berhasil) {
            return redirect()->to('/admin/siswa')->with('errors', ['db' => 'Gagal menghapus data siswa.']);
        }

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa dipindahkan ke tong sampah. Bisa dipulihkan kapan saja dari menu Recycle Bin.');
    }

    /**
     * Tampilkan daftar siswa yang sudah di-soft-delete (tong sampah).
     */
    public function trash()
    {
        $data['siswaTerhapus'] = $this->siswaModel->onlyDeleted()
            ->orderBy('deleted_at', 'DESC')
            ->findAll();

        return view('admin/siswa/trash', $data);
    }

    /**
     * Pulihkan siswa dari tong sampah.
     */
    public function restore($id_siswa)
    {
        $siswaData = $this->siswaModel->onlyDeleted()->find($id_siswa);
        if (!$siswaData) {
            return redirect()->to('/admin/siswa/trash')->with('errors', ['404' => 'Data tidak ditemukan di tong sampah.']);
        }

        $berhasil = $this->siswaModel->update($id_siswa, ['deleted_at' => null]);   

        if (!$berhasil) {
            return redirect()->to('/admin/siswa/trash')->with('errors', ['db' => 'Gagal memulihkan data siswa.']);
        }

        return redirect()->to('/admin/siswa/trash')->with('success', "Siswa {$siswaData['nama']} berhasil dipulihkan.");
    }

    /**
     * Hapus permanen siswa dari tong sampah (tidak bisa dibatalkan).
     */
    public function forceDelete($id_siswa)
    {
        $siswaData = $this->siswaModel->onlyDeleted()->find($id_siswa);
        if (!$siswaData) {
            return redirect()->to('/admin/siswa/trash')->with('errors', ['404' => 'Data tidak ditemukan di tong sampah.']);
        }

        $berhasil = $this->siswaModel->delete($id_siswa, true); // true = hard delete permanen

        if (!$berhasil) {
            return redirect()->to('/admin/siswa/trash')->with('errors', ['db' => 'Gagal menghapus data siswa secara permanen.']);
        }

        if (!empty($siswaData['foto']) && file_exists(FCPATH . 'uploads/siswa/' . $siswaData['foto'])) {
            unlink(FCPATH . 'uploads/siswa/' . $siswaData['foto']);
        }

        return redirect()->to('/admin/siswa/trash')->with('success', 'Data siswa dihapus permanen.');
    }
}
