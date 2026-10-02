<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Services\GuruService;
use InvalidArgumentException;
use Throwable;

class Guru extends BaseController
{
    protected GuruService $guru;

    public function __construct()
    {
        $this->guru = service('guruService');
    }

    public function index()
    {
        return view('admin/guru/index', $this->guru->daftar($this->request->getGet('cari')));
    }

    public function create()
    {
        return view('admin/guru/create');
    }

    public function store()
    {
        $data = $this->guru->rapikanInput($this->inputForm());

        if (!$this->validateData($data, $this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->guru->tambah($data, $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan data guru. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to('/admin/guru')->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit($id_guru)
    {
        $guru = $this->guru->cari($id_guru);

        if (!$guru) {
            return $this->tidakDitemukan();
        }

        return view('admin/guru/edit', ['guru' => $guru]);
    }

    public function update($id_guru)
    {
        if (!$this->guru->cari($id_guru)) {
            return $this->tidakDitemukan();
        }

        $data = $this->guru->rapikanInput($this->inputForm(true));

        if (!$this->validateData($data, $this->rules((int) $id_guru))) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->guru->ubah((int) $id_guru, $data, $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui data guru. Silakan coba lagi.', 'update');
        }

        return redirect()->to('/admin/guru')->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Guru tidak benar-benar dihapus, hanya dinonaktifkan.
     */
    public function delete($id_guru)
    {
        try {
            $this->guru->nonaktifkan((int) $id_guru);
        } catch (Throwable $e) {
            log_message('error', 'Gagal nonaktifkan Guru: ' . $e->getMessage());

            $pesan = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Gagal menonaktifkan guru. Silakan coba lagi.';

            return redirect()->to('/admin/guru')->with('errors', ['guru' => $pesan]);
        }

        return redirect()->to('/admin/guru')->with('success', 'Guru dinonaktifkan.');
    }

    // ================= Helper privat =================

    private function inputForm(bool $denganStatus = false): array
    {
        $kolom = ['nip', 'nuptk', 'nama', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'jabatan', 'no_hp', 'email'];

        if ($denganStatus) {
            $kolom[] = 'status';
        }

        return $this->request->getPost($kolom);
    }

    /**
     * Validasi format input. Foto tidak divalidasi di sini:
     * tipe dan ukuran dicek di FileUploadService.
     */
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

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/guru')->with('errors', ['404' => 'Data guru tidak ditemukan.']);
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Guru: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return $this->kembaliDenganError(['guru' => $pesan]);
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}