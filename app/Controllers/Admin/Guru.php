<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\UserModel;
use Config\Database;

class Guru extends BaseController
{
    protected GuruModel $guruModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->guruModel = new GuruModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('cari');

        $builder = $this->guruModel->getAllWithUser();
        if ($keyword) {
            $builder->groupStart()
                    ->like('guru.nama', $keyword)
                    ->orLike('guru.nip', $keyword)
                    ->groupEnd();
        }

        $data['guru']    = $builder->paginate(5, 'guru');
        $data['pager']   = $this->guruModel->pager;
        $data['keyword'] = $keyword;

        return view('admin/guru/index', $data);
    }

    public function create()
    {
        return view('admin/guru/create');
    }

    public function store()
    {
        $rules = [
            'username'      => 'required|min_length[4]|is_unique[users.username]',
            'password'      => 'required|min_length[6]',
            'nip'           => 'required|is_unique[guru.nip]',
            'nama'          => 'required|min_length[3]',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'email'         => 'permit_empty|valid_email|is_unique[guru.email]',
            'foto'          => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $fotoName = null;
        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $fotoName = $fotoFile->getRandomName();
            $fotoFile->move(FCPATH . 'uploads/guru', $fotoName);
        }

        $db = Database::connect();
        $db->transStart();

        $userId = $this->userModel->insert([
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id'  => 2,
            'status'   => 'Aktif',
        ]);

        $this->guruModel->skipValidation(true)->insert([
            'user_id'       => $userId,
            'nip'           => $this->request->getPost('nip'),
            'nuptk'         => $this->request->getPost('nuptk'),
            'nama'          => $this->request->getPost('nama'),
            'tempat_lahir'  => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'jabatan'       => $this->request->getPost('jabatan'),
            'no_hp'         => $this->request->getPost('no_hp'),
            'email'         => $this->request->getPost('email'),
            'foto'          => $fotoName,
            'status'        => 'Aktif',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('errors', ['db' => 'Gagal menyimpan data guru.']);
        }

        return redirect()->to('/admin/guru')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit($id_guru)
    {
        $data['guru'] = $this->guruModel->findWithUser($id_guru);

        if (!$data['guru']) {
            return redirect()->to('/admin/guru')->with('errors', ['404' => 'Data guru tidak ditemukan.']);
        }

        return view('admin/guru/edit', $data);
    }

    public function update($id_guru)
    {
        $guru = $this->guruModel->find($id_guru);
        if (!$guru) {
            return redirect()->to('/admin/guru')->with('errors', ['404' => 'Data guru tidak ditemukan.']);
        }

        $rules = [
            'nip'           => "required|is_unique[guru.nip,id_guru,{$id_guru}]",
            'nama'          => 'required|min_length[3]',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'email'         => "permit_empty|valid_email|is_unique[guru.email,id_guru,{$id_guru}]",
            'foto'          => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataUpdate = [
            'nip'           => $this->request->getPost('nip'),
            'nuptk'         => $this->request->getPost('nuptk'),
            'nama'          => $this->request->getPost('nama'),
            'tempat_lahir'  => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'jabatan'       => $this->request->getPost('jabatan'),
            'no_hp'         => $this->request->getPost('no_hp'),
            'email'         => $this->request->getPost('email'),
            'status'        => $this->request->getPost('status'),
        ];

        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            if (!empty($guru['foto']) && file_exists(FCPATH . 'uploads/guru/' . $guru['foto'])) {
                unlink(FCPATH . 'uploads/guru/' . $guru['foto']);
            }
            $fotoName = $fotoFile->getRandomName();
            $fotoFile->move(FCPATH . 'uploads/guru', $fotoName);
            $dataUpdate['foto'] = $fotoName;
        }

        $berhasil = $this->guruModel->skipValidation(true)->update($id_guru, $dataUpdate);

        if (!$berhasil) {
            return redirect()->back()->withInput()
                ->with('errors', ['update' => 'Gagal memperbarui data guru.']);
        }

        // Sinkronkan status akun login (users) mengikuti status guru
        $this->userModel->update($guru['user_id'], [
            'status' => $dataUpdate['status'],
        ]);

        return redirect()->to('/admin/guru')->with('success', 'Data guru berhasil diperbarui.');
    }

   public function delete($id_guru)
    {
        $guru = $this->guruModel->find($id_guru);
        if (!$guru) {
            return redirect()->to('/admin/guru')->with('errors', ['404' => 'Data guru tidak ditemukan.']);
        }

        $berhasil = $this->guruModel->skipValidation(true)->update($id_guru, ['status' => 'Nonaktif']);
        $this->userModel->update($guru['user_id'], ['status' => 'Nonaktif']);

        if (!$berhasil) {
            return redirect()->to('/admin/guru')->with('errors', ['delete' => 'Gagal menonaktifkan guru.']);
        }

        return redirect()->to('/admin/guru')->with('success', 'Akun guru dinonaktifkan.');
    }
}