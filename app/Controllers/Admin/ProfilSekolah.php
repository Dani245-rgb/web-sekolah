<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProfilKontenModel;

class ProfilSekolah extends BaseController
{
    protected $profilModel;

    public function __construct()
    {
        $this->profilModel = new ProfilKontenModel();
    }

    public function sejarah()
    {
        $data['item']  = $this->profilModel->getOrCreate('sejarah');
        $data['jenis'] = 'sejarah';
        $data['label'] = 'Sejarah Sekolah';
        return view('admin/profil_sekolah/teks', $data);
    }

    public function visiMisi()
    {
        $data['item']  = $this->profilModel->getOrCreate('visi_misi');
        $data['jenis'] = 'visi_misi';
        $data['label'] = 'Visi & Misi';
        return view('admin/profil_sekolah/teks', $data);
    }

    public function updateTeks($jenis)
    {
        if (!in_array($jenis, ['sejarah', 'visi_misi'], true)) {
            return redirect()->to('/admin/profil-sekolah/sejarah')->with('error', 'Jenis tidak valid.');
        }

        $rules = [
            'judul'  => 'required|max_length[255]',
            'konten' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $item = $this->profilModel->getOrCreate($jenis);

        $this->profilModel->update($item['id'], [
            'judul'  => $this->request->getPost('judul'),
            'konten' => $this->request->getPost('konten'),
        ]);

        return redirect()->to('/admin/profil-sekolah/' . ($jenis === 'sejarah' ? 'sejarah' : 'visi-misi'))
            ->with('success', 'Berhasil disimpan.');
    }

    public function kepalaSekolah()
    {
        $data['item'] = $this->profilModel->getOrCreate('kepala_sekolah');
        return view('admin/profil_sekolah/kepala_sekolah', $data);
    }

    public function updateKepalaSekolah()
    {
        $rules = [
            'nama'    => 'required|max_length[255]',
            'jabatan' => 'required|max_length[100]',
            'konten'  => 'required',
        ];

        $item = $this->profilModel->getOrCreate('kepala_sekolah');

        if ($this->request->getFile('foto')->isValid()) {
            $rules['foto'] = 'is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png,image/webp]|max_size[foto,2048]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'nama'    => $this->request->getPost('nama'),
            'jabatan' => $this->request->getPost('jabatan'),
            'konten'  => $this->request->getPost('konten'),
        ];

        $file = $this->request->getFile('foto');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/profil', $newName);
            $updateData['foto'] = $newName;

            if ($item['foto'] && is_file(FCPATH . 'uploads/profil/' . $item['foto'])) {
                unlink(FCPATH . 'uploads/profil/' . $item['foto']);
            }
        }

        $this->profilModel->update($item['id'], $updateData);

        return redirect()->to('/admin/profil-sekolah/kepala-sekolah')->with('success', 'Berhasil disimpan.');
    }
}