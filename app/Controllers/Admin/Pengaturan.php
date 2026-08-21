<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengaturanModel;

class Pengaturan extends BaseController
{
    protected PengaturanModel $model;

    public function __construct()
    {
        $this->model = new PengaturanModel();
    }

    public function index()
    {
        $data['pengaturan'] = $this->model->getPengaturan();
        return view('admin/pengaturan/index', $data);
    }

    public function update()
    {
        $pengaturan = $this->model->getPengaturan();

        $rules = [
            'nama_sekolah' => 'required|min_length[3]|max_length[150]',
            'email'        => 'permit_empty|valid_email',
            'logo'         => 'permit_empty|is_image[logo]|mime_in[logo,image/jpg,image/jpeg,image/png,image/webp]|max_size[logo,2048]',
            'favicon'      => 'permit_empty|is_image[favicon]|mime_in[favicon,image/jpg,image/jpeg,image/png]|max_size[favicon,512]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataUpdate = [
            'nama_sekolah' => $this->request->getPost('nama_sekolah'),
            'alamat'       => $this->request->getPost('alamat'),
            'telepon'      => $this->request->getPost('telepon'),
            'email'        => $this->request->getPost('email'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        // Upload logo (kalau ada file baru)
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $namaFile = $logo->getRandomName() . '.' . $logo->getExtension();
            $logo->move(FCPATH . 'assets/uploads/sekolah', $namaFile);
            $dataUpdate['logo'] = $namaFile;
        }

        // Upload favicon (kalau ada file baru)
        $favicon = $this->request->getFile('favicon');
        if ($favicon && $favicon->isValid() && !$favicon->hasMoved()) {
            $namaFileFavicon = $favicon->getRandomName() . '.' . $favicon->getExtension();
            $favicon->move(FCPATH . 'assets/uploads/sekolah', $namaFileFavicon);
            $dataUpdate['favicon'] = $namaFileFavicon;
        }

        $this->model->update($pengaturan['id'], $dataUpdate);

        (new \App\Models\AuditLogModel())->catat(
            session()->get('id_user'),
            session()->get('username'),
            'update_pengaturan',
            'Mengubah pengaturan identitas sekolah.'
        );

        return redirect()->to('/admin/pengaturan')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
