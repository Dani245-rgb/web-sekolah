<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AnggotaOrganisasiService;
use InvalidArgumentException;
use Throwable;

class AnggotaOrganisasi extends BaseController
{
    protected AnggotaOrganisasiService $anggota;

    public function __construct()
    {
        $this->anggota = service('anggotaOrganisasiService');
    }

    public function index($idOrganisasi)
    {
        $organisasi = $this->anggota->cariOrganisasi($idOrganisasi);
        if (!$organisasi) {
            return $this->organisasiTidakDitemukan();
        }

        return view('admin/anggota_organisasi/index', [
            'organisasi' => $organisasi,
            'anggota'    => $this->anggota->daftar((int) $idOrganisasi),
        ]);
    }

    public function create($idOrganisasi)
    {
        $organisasi = $this->anggota->cariOrganisasi($idOrganisasi);
        if (!$organisasi) {
            return $this->organisasiTidakDitemukan();
        }

        return view('admin/anggota_organisasi/form', ['organisasi' => $organisasi, 'item' => null]);
    }

    public function store($idOrganisasi)
    {
        if (!$this->anggota->cariOrganisasi($idOrganisasi)) {
            return $this->organisasiTidakDitemukan();
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->anggota->tambah((int) $idOrganisasi, $this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan anggota. Silakan coba lagi.', 'simpan');
        }

        return redirect()->to($this->urlDaftar($idOrganisasi))->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function edit($idOrganisasi, $id)
    {
        $organisasi = $this->anggota->cariOrganisasi($idOrganisasi);
        if (!$organisasi) {
            return $this->organisasiTidakDitemukan();
        }

        $item = $this->anggota->cariAnggota((int) $idOrganisasi, $id);
        if (!$item) {
            return $this->anggotaTidakDitemukan($idOrganisasi);
        }

        return view('admin/anggota_organisasi/form', ['organisasi' => $organisasi, 'item' => $item]);
    }

    public function update($idOrganisasi, $id)
    {
        if (!$this->anggota->cariAnggota((int) $idOrganisasi, $id)) {
            return $this->anggotaTidakDitemukan($idOrganisasi);
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->anggota->ubah((int) $idOrganisasi, (int) $id, $this->inputForm(), $this->request->getFile('foto'));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal memperbarui anggota. Silakan coba lagi.', 'update');
        }

        return redirect()->to($this->urlDaftar($idOrganisasi))->with('success', 'Anggota berhasil diperbarui.');
    }

    public function delete($idOrganisasi, $id)
    {
        try {
            $this->anggota->hapus((int) $idOrganisasi, (int) $id);
        } catch (Throwable $e) {
            log_message('error', 'Gagal hapus Anggota Organisasi: ' . $e->getMessage());

            return redirect()->to($this->urlDaftar($idOrganisasi))->with('error', 'Gagal menghapus anggota. Silakan coba lagi.');
        }

        return redirect()->to($this->urlDaftar($idOrganisasi))->with('success', 'Anggota berhasil dihapus.');
    }

    // ================= Helper privat =================

    private function inputForm(): array
    {
        return $this->request->getPost(['nama', 'jabatan', 'urutan']);
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

    private function anggotaTidakDitemukan($idOrganisasi)
    {
        return redirect()->to($this->urlDaftar($idOrganisasi))->with('error', 'Data tidak ditemukan.');
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Anggota Organisasi: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return $this->kembaliDenganError(['anggota' => $pesan]);
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}