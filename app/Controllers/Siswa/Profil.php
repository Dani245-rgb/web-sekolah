<?php

namespace App\Controllers\Siswa;

use App\Controllers\BaseController;
use App\Models\SiswaModel;

class Profil extends BaseController
{
    protected function getSiswaLogin()
    {
        $siswaModel = new SiswaModel();
        $siswa = $siswaModel->select('siswa.*, kelas.nama_kelas')
            ->join('kelas_siswa', 'kelas_siswa.id_siswa = siswa.id_siswa')
            ->join('kelas', 'kelas.id_kelas = kelas_siswa.id_kelas')
            ->where('siswa.user_id', session()->get('id_user'))
            ->first();

        if (!$siswa) {
            throw new \RuntimeException('DATA_SISWA_TIDAK_DITEMUKAN');
        }

        return $siswa;
    }

    public function index()
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data siswa Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        return view('siswa/profil/index', ['siswa' => $siswa]);
    }

    public function edit()
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data siswa Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        return view('siswa/profil/edit', ['siswa' => $siswa]);
    }

    public function update()
    {
        try {
            $siswa = $this->getSiswaLogin();
        } catch (\RuntimeException $e) {
            return redirect()->to('/logout')
                ->with('errors', ['akun' => 'Data siswa Anda tidak ditemukan. Silakan hubungi Admin.']);
        }

        $siswaModel = new SiswaModel();

        $rules = [
            'email'   => 'permit_empty|valid_email',
            'no_hp_ortu' => 'permit_empty|max_length[20]',
            'alamat'  => 'permit_empty|max_length[500]',
            'foto'    => 'permit_empty|max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataUpdate = [
            'email'      => $this->request->getPost('email') ?: null,
            'no_hp_ortu' => $this->request->getPost('no_hp_ortu'),
            'alamat'     => $this->request->getPost('alamat'),
        ];

        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $namaFotoBaru = 'siswa_' . $siswa['id_siswa'] . '_' . time() . '.' . $fotoFile->getExtension();
            $fotoFile->move(FCPATH . 'assets/uploads/siswa', $namaFotoBaru);

            if (!empty($siswa['foto']) && file_exists(FCPATH . 'assets/uploads/siswa/' . $siswa['foto'])) {
                @unlink(FCPATH . 'assets/uploads/siswa/' . $siswa['foto']);
            }

            $dataUpdate['foto'] = $namaFotoBaru;
        }

        $siswaModel->update($siswa['id_siswa'], $dataUpdate);

        return redirect()->to('/siswa/profil')->with('success', 'Profil berhasil diperbarui.');
    }
}