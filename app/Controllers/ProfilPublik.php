<?php

namespace App\Controllers;

use App\Models\OrganisasiModel;
use App\Models\ProfilKontenModel;

class ProfilPublik extends BaseController
{
    protected ProfilKontenModel $profilModel;
    protected OrganisasiModel $organisasiModel;

    public function __construct()
    {
        $this->profilModel     = new ProfilKontenModel();
        $this->organisasiModel = new OrganisasiModel();
    }

    public function sejarah()
    {
        return $this->tampilkanTeks('sejarah');
    }

    public function visiMisi()
    {
        return $this->tampilkanTeks('visi_misi');
    }

    public function kepalaSekolah()
    {
        $data['item'] = $this->profilModel->getByJenis('kepala_sekolah');

        return view('profil_publik/kepala_sekolah', $data);
    }

    public function struktur()
    {
        $data['organisasi'] = $this->organisasiModel->getPublishedWithAnggota();

        return view('profil_publik/struktur', $data);
    }

    private function tampilkanTeks(string $jenis)
    {
        $data['item'] = $this->profilModel->getByJenis($jenis);

        return view('profil_publik/teks', $data);
    }
}