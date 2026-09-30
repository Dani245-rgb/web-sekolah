<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProfilKontenModel;
use App\Services\FileUploadService;
use InvalidArgumentException;
use RuntimeException;

class ProfilSekolah extends BaseController
{
    private const FOLDER_FOTO = 'profil'; // public/uploads/profil

    protected ProfilKontenModel $profilModel;
    protected FileUploadService $fileUpload;

    public function __construct()
    {
        helper('teks');

        $this->profilModel = new ProfilKontenModel();
        $this->fileUpload  = service('fileUploadService');
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
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $item = $this->profilModel->getOrCreate($jenis);

        $berhasil = $this->profilModel->update($item['id'], [
            'judul'  => rapikan_teks((string) $this->request->getPost('judul')),
            'konten' => $this->request->getPost('konten'),
        ]);

        if (!$berhasil) {
            return $this->kembaliDenganError($this->profilModel->errors());
        }

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

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $item = $this->profilModel->getOrCreate('kepala_sekolah');

        $dataUpdate = [
            'nama'    => rapikan_teks((string) $this->request->getPost('nama')),
            'jabatan' => rapikan_teks((string) $this->request->getPost('jabatan')),
            'konten'  => $this->request->getPost('konten'),
        ];

        // Foto baru (opsional). Kalau tidak diisi, foto lama dipertahankan.
        try {
            $fotoBaru = $this->fileUpload->simpan($this->request->getFile('foto'), self::FOLDER_FOTO);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        if ($fotoBaru !== null) {
            $dataUpdate['foto'] = $fotoBaru;
        }

        if (!$this->profilModel->update($item['id'], $dataUpdate)) {
            $this->fileUpload->hapus($fotoBaru, self::FOLDER_FOTO);
            return $this->kembaliDenganError($this->profilModel->errors());
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->fileUpload->hapus($item['foto'], self::FOLDER_FOTO);
        }

        return redirect()->to('/admin/profil-sekolah/kepala-sekolah')->with('success', 'Berhasil disimpan.');
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}