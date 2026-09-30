<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Services\FileUploadService;
use InvalidArgumentException;
use RuntimeException;

class Guru extends BaseController
{
    private const FOLDER    = 'guru'; // public/uploads/guru
    private const MIME_FOTO = ['image/jpeg', 'image/png'];

    protected GuruModel $guruModel;
    protected FileUploadService $fileUpload;

    public function __construct()
    {
        helper('teks');

        $this->guruModel  = new GuruModel();
        $this->fileUpload = service('fileUploadService');
    }

    public function index()
    {
        $cari    = $this->request->getGet('cari');
        $keyword = is_string($cari) && trim($cari) !== '' ? trim($cari) : null;

        $data['guru']    = $this->guruModel->cari($keyword)->paginate(5, 'guru');
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
        $data = $this->dataDariForm();

        if (!$this->validateData($data, $this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $foto = $this->fileUpload->simpanKompres($this->request->getFile('foto'), self::FOLDER, self::MIME_FOTO);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        $data['foto']   = $foto;
        $data['status'] = 'Aktif';

        $idGuru = $this->guruModel->skipValidation(true)->insert($data);

        if (!$idGuru) {
            // Data gagal masuk DB, file yang sudah terlanjur diupload dibuang lagi
            $this->fileUpload->hapus($foto, self::FOLDER);

            $errors     = $this->guruModel->errors();
            $pesanError = $errors ? implode('; ', $errors) : 'Gagal menyimpan data guru.';

            return $this->kembaliDenganError(['db' => $pesanError]);
        }

        return redirect()->to('/admin/guru')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit($id_guru)
    {
        $data['guru'] = $this->guruModel->findWithUser($id_guru);

        if (!$data['guru']) {
            return $this->tidakDitemukan();
        }

        return view('admin/guru/edit', $data);
    }

    public function update($id_guru)
    {
        $guru = $this->guruModel->find($id_guru);
        if (!$guru) {
            return $this->tidakDitemukan();
        }

        $data           = $this->dataDariForm();
        $data['status'] = (string) $this->request->getPost('status');

        if (!$this->validateData($data, $this->rules((int) $id_guru))) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        // Foto baru (opsional). Kalau tidak diisi, foto lama dipertahankan.
        try {
            $fotoBaru = $this->fileUpload->simpanKompres($this->request->getFile('foto'), self::FOLDER, self::MIME_FOTO);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $this->kembaliDenganError(['foto' => $e->getMessage()]);
        }

        if ($fotoBaru !== null) {
            $data['foto'] = $fotoBaru;
        }

        if (!$this->guruModel->skipValidation(true)->update($id_guru, $data)) {
            $this->fileUpload->hapus($fotoBaru, self::FOLDER);
            return $this->kembaliDenganError(['update' => 'Gagal memperbarui data guru.']);
        }

        // Foto lama baru dihapus SETELAH update DB sukses
        if ($fotoBaru !== null) {
            $this->fileUpload->hapus($guru['foto'], self::FOLDER);
        }

        return redirect()->to('/admin/guru')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function delete($id_guru)
    {
        $guru = $this->guruModel->find($id_guru);
        if (!$guru) {
            return $this->tidakDitemukan();
        }

        $berhasil = $this->guruModel->skipValidation(true)->update($id_guru, ['status' => 'Nonaktif']);

        if (!$berhasil) {
            return redirect()->to('/admin/guru')->with('errors', ['delete' => 'Gagal menonaktifkan guru.']);
        }

        return redirect()->to('/admin/guru')->with('success', 'Guru dinonaktifkan.');
    }

    /**
     * Data dari form yang sudah dirapikan, dipakai bareng oleh store() dan update().
     * email, nuptk, dan tanggal_lahir yang kosong disimpan sebagai NULL.
     */
    private function dataDariForm(): array
    {
        return [
            'nip'           => rapikan_teks((string) $this->request->getPost('nip')),
            'nuptk'         => $this->atauNull(rapikan_teks((string) $this->request->getPost('nuptk'))),
            'nama'          => rapikan_teks((string) $this->request->getPost('nama')),
            'tempat_lahir'  => rapikan_teks((string) $this->request->getPost('tempat_lahir')),
            'tanggal_lahir' => $this->atauNull((string) $this->request->getPost('tanggal_lahir')),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin'),
            'jabatan'       => rapikan_teks((string) $this->request->getPost('jabatan')),
            'no_hp'         => rapikan_teks((string) $this->request->getPost('no_hp')),
            'email'         => $this->atauNull(rapikan_teks((string) $this->request->getPost('email'))),
        ];
    }

    private function rules(?int $id = null): array
    {
        $kecuali = $id !== null ? ",id_guru,{$id}" : '';

        $rules = [
            'nip'           => "required|max_length[30]|is_unique[guru.nip{$kecuali}]",
            'nama'          => 'required|min_length[3]|max_length[100]',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'email'         => "permit_empty|valid_email|is_unique[guru.email{$kecuali}]",
            'tanggal_lahir' => 'permit_empty|valid_date',
        ];

        if ($id !== null) {
            $rules['status'] = 'required|in_list[' . implode(',', GuruModel::STATUS) . ']';
        }

        return $rules;
    }

    private function atauNull(string $nilai): ?string
    {
        return $nilai !== '' ? $nilai : null;
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/guru')->with('errors', ['404' => 'Data guru tidak ditemukan.']);
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}