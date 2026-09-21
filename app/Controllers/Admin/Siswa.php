<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\UserModel;
use App\Libraries\ImageCompressor;
use Config\Database;

class Siswa extends BaseController
{
    protected SiswaModel $siswaModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        $this->userModel  = new UserModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('cari');

        $builder = $this->siswaModel->getAll();

        if ($keyword) {
            $builder->groupStart()
                ->like('nama', $keyword)
                ->orLike('nis', $keyword)
                ->orLike('nisn', $keyword)
                ->groupEnd();
        }

        $data['siswa']   = $builder->paginate(50);
        $data['pager']   = $this->siswaModel->pager;
        $data['keyword'] = $keyword;

        return view('admin/siswa/index', $data);
    }

    public function create()
    {
        return view('admin/siswa/create');
    }

    public function store()
    {
        $rules = [
            'nis'            => 'required|max_length[20]|is_unique[siswa.nis]',
            'nisn'           => 'required|max_length[20]|is_unique[siswa.nisn]',
            'nama'           => 'required|min_length[3]|max_length[100]',
            'tanggal_lahir'  => 'required|valid_date',
            'jenis_kelamin'  => 'required|in_list[L,P]',
            'email'          => 'permit_empty|valid_email',
            'foto'           => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nis           = $this->request->getPost('nis');
        $tanggalLahir  = $this->request->getPost('tanggal_lahir'); // format: YYYY-MM-DD
        $passwordAwal  = date('dmY', strtotime($tanggalLahir)); // ddmmyyyy

        $fotoName = null;
        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $fotoName = $fotoFile->getRandomName();
            $compressor = new ImageCompressor();
            $compressor->compressAndSave($fotoFile->getTempName(), FCPATH . 'uploads/siswa/' . $fotoName);
        }

        $db = Database::connect();

        $userId = $this->userModel->insert([
            'username'             => $nis,
            'password'             => password_hash($passwordAwal, PASSWORD_DEFAULT),
            'role_id'              => 3,
            'status'               => 'Aktif',
            'must_change_password' => true,
        ]);

        if (!$userId) {
            $db->transRollback();
            $errors = $this->userModel->errors();
            $pesanError = $errors ? implode('; ', $errors) : 'Gagal membuat akun user.';
            return redirect()->back()->withInput()->with('errors', ['user' => $pesanError]);
        }

        $idSiswa = $this->siswaModel->skipValidation(true)->insert([
            'user_id'        => $userId,
            'nis'            => $nis,
            'nisn'           => $this->request->getPost('nisn'),
            'nama'           => $this->request->getPost('nama'),
            'tempat_lahir'   => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'  => $tanggalLahir,
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

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('errors', ['db' => 'Gagal menyimpan data siswa.']);
        }

        return redirect()->to('/admin/siswa')
            ->with('success', "Siswa {$this->request->getPost('nama')} berhasil ditambahkan. Username: {$nis}, Password awal: {$passwordAwal}");
    }

    public function edit($id_siswa)
    {
        $data['siswaData'] = $this->siswaModel->find($id_siswa);

        if (!$data['siswaData']) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

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

        // Sinkronkan username akun login kalau NIS diubah
        $this->userModel->update($siswaData['user_id'], ['username' => $dataUpdate['nis']]);

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

        $db = Database::connect();
        $db->transStart();

        // Soft delete: data siswa & akun login dipindah ke "tong sampah", bukan dihapus permanen.
        // Foto TIDAK dihapus dari server, disimpan sampai nanti dihapus permanen dari Recycle Bin.
        $this->siswaModel->delete($id_siswa);
        $this->userModel->delete($siswaData['user_id']);

        $db->transComplete();

        if ($db->transStatus() === false) {
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

        $db = Database::connect();
        $db->transStart();

        $this->siswaModel->update($id_siswa, ['deleted_at' => null]);
        $this->userModel->update($siswaData['user_id'], ['deleted_at' => null]);

        $db->transComplete();

        if ($db->transStatus() === false) {
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

        $db = Database::connect();
        $db->transStart();

        $this->siswaModel->delete($id_siswa, true);   // true = hard delete permanen
        $this->userModel->delete($siswaData['user_id'], true);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/siswa/trash')->with('errors', ['db' => 'Gagal menghapus data siswa secara permanen.']);
        }

        if (!empty($siswaData['foto']) && file_exists(FCPATH . 'uploads/siswa/' . $siswaData['foto'])) {
            unlink(FCPATH . 'uploads/siswa/' . $siswaData['foto']);
        }

        return redirect()->to('/admin/siswa/trash')->with('success', 'Data siswa dihapus permanen.');
    }
}
