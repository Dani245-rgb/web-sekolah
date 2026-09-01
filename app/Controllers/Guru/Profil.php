<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;

class Profil extends BaseController
{
    protected function getGuruLogin()
    {
        $userId    = session()->get('id_user');
        $guruModel = new GuruModel();
        $guru      = $guruModel->findWithUser(
            $guruModel->where('user_id', $userId)->first()['id_guru'] ?? 0
        );

        if (!$guru) {
            throw new \RuntimeException('DATA_GURU_TIDAK_DITEMUKAN');
        }

        return $guru;
    }

    public function index()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        return view('guru/profil/index', ['guru' => $guru]);
    }

    public function edit()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        return view('guru/profil/edit', ['guru' => $guru]);
    }

    public function update()
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $guruModel = new GuruModel();

        $rules = [
            'nama'          => 'required|min_length[3]|max_length[100]',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'email'         => 'permit_empty|valid_email|is_unique[guru.email,id_guru,' . $guru['id_guru'] . ']',
            'tanggal_lahir' => 'permit_empty|valid_date',
            'no_hp'         => 'permit_empty|max_length[20]',
            'foto'          => 'permit_empty|max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataUpdate = [
            'nama'          => $this->request->getPost('nama'),
            'tempat_lahir'  => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir') ?: null,
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'no_hp'         => $this->request->getPost('no_hp'),
            'email'         => $this->request->getPost('email') ?: null,
        ];

        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $namaFotoBaru = 'guru_' . $guru['id_guru'] . '_' . time() . '.' . $fotoFile->getExtension();
            $fotoFile->move(FCPATH . 'assets/uploads/guru', $namaFotoBaru);

            // Hapus foto lama kalau ada, biar tidak menumpuk file sampah
            if (!empty($guru['foto']) && file_exists(FCPATH . 'assets/uploads/guru/' . $guru['foto'])) {
                @unlink(FCPATH . 'assets/uploads/guru/' . $guru['foto']);
            }

            $dataUpdate['foto'] = $namaFotoBaru;
        }

        $guruModel->update($guru['id_guru'], $dataUpdate);

        return redirect()->to('/guru/profil')->with('success', 'Profil berhasil diperbarui.');
    }
}