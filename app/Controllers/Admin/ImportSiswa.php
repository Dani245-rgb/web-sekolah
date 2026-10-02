<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ImportSiswaService;
use InvalidArgumentException;
use Throwable;

class ImportSiswa extends BaseController
{
    protected ImportSiswaService $import;

    public function __construct()
    {
        $this->import = service('importSiswaService');
    }

    // Halaman upload + deteksi job belum selesai
    public function index()
    {
        return view('admin/siswa/import', ['jobBelumSelesai' => $this->import->jobBelumSelesai()]);
    }

    // Download template kosong
    public function template()
    {
        return $this->response->download('template_import_siswa.xlsx', $this->import->buatTemplate(), true);
    }

    // Terima file upload, hitung total baris, buat job baru
    public function upload()
    {
        try {
            $hasil = $this->import->terimaUpload($this->request->getFile('file_excel'));
        } catch (InvalidArgumentException $e) {
            return $this->jsonDenganCsrf(['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            log_message('error', 'Gagal upload import siswa: ' . $e->getMessage());
            return $this->jsonDenganCsrf(['error' => 'Gagal memproses file. Silakan coba lagi.']);
        }

        return $this->jsonDenganCsrf($hasil);
    }

    // Proses 1 batch (dipanggil berulang oleh JS)
    public function proses($id_import_log)
    {
        // Batas resource khusus proses import (tidak mengganggu setting PHP global)
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        try {
            $hasil = $this->import->prosesBatch((int) $id_import_log);
        } catch (InvalidArgumentException $e) {
            return $this->jsonDenganCsrf(['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            log_message('error', 'Gagal proses batch import siswa: ' . $e->getMessage());
            return $this->jsonDenganCsrf(['error' => 'Terjadi kesalahan saat memproses batch. Silakan coba lanjutkan.']);
        }

        return $this->jsonDenganCsrf($hasil);
    }

    // Cek status job (progress bar dan deteksi resume)
    public function status($id_import_log)
    {
        $status = $this->import->statusJob((int) $id_import_log);

        return $this->response->setJSON($status ?? ['error' => 'Job tidak ditemukan.']);
    }

    // Token CSRF terbaru untuk dikirim balik ke JS
    private function jsonDenganCsrf(array $data)
    {
        return $this->response->setJSON(array_merge($data, [
            'csrf_name' => csrf_token(),
            'csrf_hash' => csrf_hash(),
        ]));
    }
}