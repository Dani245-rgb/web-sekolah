<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\PengaturanModel;
use App\Services\FileUploadService;
use InvalidArgumentException;
use RuntimeException;

class Pengaturan extends BaseController
{
    private const FOLDER       = 'assets/uploads/sekolah'; // public/assets/uploads/sekolah
    private const MIME_FAVICON = ['image/jpeg', 'image/png'];
    private const MAKS_FAVICON = 512 * 1024; // 512KB

    protected PengaturanModel $model;
    protected FileUploadService $fileUpload;

    public function __construct()
    {
        helper('teks');

        $this->model      = new PengaturanModel();
        $this->fileUpload = service('fileUploadService');
    }

    public function index()
    {
        $data['pengaturan'] = $this->model->getPengaturan();
        return view('admin/pengaturan/index', $data);
    }

    public function update()
    {
        $pengaturan = $this->model->getPengaturan();
        if (!$pengaturan) {
            return redirect()->to('/admin/pengaturan')->with('error', 'Data pengaturan belum ada di database.');
        }

        $rules = [
            'nama_sekolah' => 'required|min_length[3]|max_length[150]',
            'email'        => 'permit_empty|valid_email',
        ];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $dataUpdate = [
            'nama_sekolah' => rapikan_teks((string) $this->request->getPost('nama_sekolah')),
            'alamat'       => $this->request->getPost('alamat'),
            'telepon'      => rapikan_teks((string) $this->request->getPost('telepon')),
            'email'        => $this->request->getPost('email'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        // Logo & favicon (opsional). Kalau tidak diisi, file lama dipertahankan.
        try {
            $logoBaru = $this->fileUpload->simpan($this->request->getFile('logo'), self::FOLDER);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['logo' => $e->getMessage()]);
        }

        try {
            $faviconBaru = $this->fileUpload->simpan(
                $this->request->getFile('favicon'),
                self::FOLDER,
                self::MIME_FAVICON,
                self::MAKS_FAVICON
            );
        } catch (InvalidArgumentException | RuntimeException $e) {
            // Logo sudah terlanjur diupload, buang lagi
            $this->fileUpload->hapus($logoBaru, self::FOLDER);
            return $this->kembaliDenganError(['favicon' => $e->getMessage()]);
        }

        if ($logoBaru !== null) {
            $dataUpdate['logo'] = $logoBaru;
        }
        if ($faviconBaru !== null) {
            $dataUpdate['favicon'] = $faviconBaru;
        }

        if (!$this->model->update($pengaturan['id'], $dataUpdate)) {
            $this->fileUpload->hapus($logoBaru, self::FOLDER);
            $this->fileUpload->hapus($faviconBaru, self::FOLDER);
            return $this->kembaliDenganError($this->model->errors());
        }

        // File lama baru dihapus SETELAH update DB sukses
        if ($logoBaru !== null) {
            $this->fileUpload->hapus($pengaturan['logo'], self::FOLDER);
        }
        if ($faviconBaru !== null) {
            $this->fileUpload->hapus($pengaturan['favicon'], self::FOLDER);
        }

        (new AuditLogModel())->catat(
            session()->get('id_user'),
            session()->get('username'),
            'update_pengaturan',
            'Mengubah pengaturan identitas sekolah.'
        );

        return redirect()->to('/admin/pengaturan')->with('success', 'Pengaturan berhasil disimpan.');
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}