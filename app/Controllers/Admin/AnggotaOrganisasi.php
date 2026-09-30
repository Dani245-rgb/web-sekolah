<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnggotaOrganisasiModel;
use App\Models\OrganisasiModel;
use App\Services\FileUploadService;
use InvalidArgumentException;
use RuntimeException;

class AnggotaOrganisasi extends BaseController
{
    private const FOLDER = 'organisasi'; // public/uploads/organisasi

    protected AnggotaOrganisasiModel $anggotaModel;
    protected OrganisasiModel $organisasiModel;
    protected FileUploadService $fileUpload;

    public function __construct()
    {
        helper('teks');

        $this->anggotaModel    = new AnggotaOrganisasiModel();
        $this->organisasiModel = new OrganisasiModel();
        $this->fileUpload      = service('fileUploadService');
    }

    public function index($idOrganisasi)
    {
        $organisasi = $this->organisasiModel->find($idOrganisasi);
        if (!$organisasi) {
            return $this->organisasiTidakDitemukan();
        }

        $data['organisasi'] = $organisasi;
        $data['anggota']    = $this->anggotaModel->where('id_organisasi', $idOrganisasi)
            ->orderBy('urutan', 'ASC')
            ->findAll();

        return view('admin/anggota_organisasi/index', $data);
    }

    public function create($idOrganisasi)
    {
        $organisasi = $this->organisasiModel->find($idOrganisasi);
        if (!$organisasi) {
            return $this->organisasiTidakDitemukan();
        }

        $data['organisasi'] = $organisasi;
        $data['item']       = null;

        return view('admin/anggota_organisasi/form', $data);
    }

    public function store($idOrganisasi)
    {
        if (!$this->organisasiModel->find($idOrganisasi)) {
            return $this->organisasiTidakDitemukan();
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        // Foto opsional. Kalau tidak diupload, hasilnya null.
        try {
            $foto = $this->fileUpload->simpan($this->request->getFile('foto'), self::FOLDER);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        $berhasil = $this->anggotaModel->insert([
            'id_organisasi' => (int) $idOrganisasi,
            'nama'          => rapikan_teks((string) $this->request->getPost('nama')),
            'jabatan'       => rapikan_teks((string) $this->request->getPost('jabatan')),
            'foto'          => $foto,
            'urutan'        => (int) $this->request->getPost('urutan'),
        ]);

        if (!$berhasil) {
            $this->fileUpload->hapus($foto, self::FOLDER);
            return $this->kembaliDenganError($this->anggotaModel->errors());
        }

        return redirect()->to($this->urlDaftar($idOrganisasi))->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit($idOrganisasi, $id)
    {
        $organisasi = $this->organisasiModel->find($idOrganisasi);
        if (!$organisasi) {
            return $this->organisasiTidakDitemukan();
        }

        $item = $this->cariAnggota($idOrganisasi, $id);
        if (!$item) {
            return redirect()->to($this->urlDaftar($idOrganisasi))->with('error', 'Data tidak ditemukan.');
        }

        $data['organisasi'] = $organisasi;
        $data['item']       = $item;

        return view('admin/anggota_organisasi/form', $data);
    }

    public function update($idOrganisasi, $id)
    {
        $item = $this->cariAnggota($idOrganisasi, $id);
        if (!$item) {
            return redirect()->to($this->urlDaftar($idOrganisasi))->with('error', 'Data tidak ditemukan.');
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $dataUpdate = [
            'nama'    => rapikan_teks((string) $this->request->getPost('nama')),
            'jabatan' => rapikan_teks((string) $this->request->getPost('jabatan')),
            'urutan'  => (int) $this->request->getPost('urutan'),
        ];

        // Foto baru (opsional). Kalau tidak diisi, foto lama dipertahankan.
        try {
            $fotoBaru = $this->fileUpload->simpan($this->request->getFile('foto'), self::FOLDER);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        if ($fotoBaru !== null) {
            $dataUpdate['foto'] = $fotoBaru;
        }

        if (!$this->anggotaModel->update($id, $dataUpdate)) {
            $this->fileUpload->hapus($fotoBaru, self::FOLDER);
            return $this->kembaliDenganError($this->anggotaModel->errors());
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->fileUpload->hapus($item['foto'], self::FOLDER);
        }

        return redirect()->to($this->urlDaftar($idOrganisasi))->with('success', 'Anggota berhasil diperbarui.');
    }

    public function delete($idOrganisasi, $id)
    {
        $item = $this->cariAnggota($idOrganisasi, $id);

        if ($item) {
            $this->anggotaModel->delete($id);

            // File dihapus SETELAH record DB terhapus
            $this->fileUpload->hapus($item['foto'], self::FOLDER);
        }

        return redirect()->to($this->urlDaftar($idOrganisasi))->with('success', 'Anggota berhasil dihapus.');
    }

    /**
     * Cari anggota, sekaligus pastikan dia memang milik organisasi di URL.
     */
    private function cariAnggota($idOrganisasi, $id): ?array
    {
        $item = $this->anggotaModel->find($id);

        if (!$item || (int) $item['id_organisasi'] !== (int) $idOrganisasi) {
            return null;
        }

        return $item;
    }

    private function rules(): array
    {
        return [
            'nama'    => 'required|min_length[2]|max_length[255]',
            'jabatan' => 'required|max_length[100]',
            'urutan'  => 'permit_empty|is_natural',
        ];
    }

    private function urlDaftar($idOrganisasi): string
    {
        return '/admin/organisasi/' . $idOrganisasi . '/anggota';
    }

    private function organisasiTidakDitemukan()
    {
        return redirect()->to('/admin/organisasi')->with('error', 'Organisasi tidak ditemukan.');
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}