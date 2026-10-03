<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ProfilSekolahService;
use InvalidArgumentException;
use Throwable;

class ProfilSekolah extends BaseController
{
    protected ProfilSekolahService $profil;

    public function __construct()
    {
        $this->profil = service('profilSekolahService');
    }

    public function sejarah()
    {
        return $this->tampilTeks('sejarah', 'Sejarah Sekolah');
    }

    public function visiMisi()
    {
        return $this->tampilTeks('visi_misi', 'Visi & Misi');
    }

    public function updateTeks($jenis)
    {
        if (!$this->profil->jenisTeksValid((string) $jenis)) {
            return redirect()->to('/admin/profil-sekolah/sejarah')->with('error', 'Jenis tidak valid.');
        }

        $rules = [
            'judul'  => 'required|max_length[255]',
            'konten' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        try {
            $this->profil->simpanTeks($jenis, $this->request->getPost(['judul', 'konten']));
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan. Silakan coba lagi.', 'simpan teks');
        }

        return redirect()->to('/admin/profil-sekolah/' . ($jenis === 'sejarah' ? 'sejarah' : 'visi-misi'))
            ->with('success', 'Berhasil disimpan.');
    }

    public function kepalaSekolah()
    {
        return view('admin/profil_sekolah/kepala_sekolah', ['item' => $this->profil->ambil('kepala_sekolah')]);
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

        try {
            $this->profil->simpanKepalaSekolah(
                $this->request->getPost(['nama', 'jabatan', 'konten']),
                $this->request->getFile('foto')
            );
        } catch (Throwable $e) {
            return $this->gagal($e, 'Gagal menyimpan. Silakan coba lagi.', 'simpan kepala sekolah');
        }

        return redirect()->to('/admin/profil-sekolah/kepala-sekolah')->with('success', 'Berhasil disimpan.');
    }

    // ================= Helper privat =================

    private function tampilTeks(string $jenis, string $label)
    {
        return view('admin/profil_sekolah/teks', [
            'item'  => $this->profil->ambil($jenis),
            'jenis' => $jenis,
            'label' => $label,
        ]);
    }

    /**
     * InvalidArgumentException = pesan aman ditampilkan ke user. Selain itu: log + pesan umum.
     */
    private function gagal(Throwable $e, string $pesanUmum, string $aksi)
    {
        log_message('error', "Gagal {$aksi} Profil Sekolah: " . $e->getMessage());

        $pesan = $e instanceof InvalidArgumentException ? $e->getMessage() : $pesanUmum;

        return $this->kembaliDenganError(['profil' => $pesan]);
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}