<?php

namespace App\Controllers;

use App\Models\ProfilKontenModel;
use App\Models\OrganisasiModel;

class ProfilPublik extends BaseController
{
    public function sejarah()
    {
        $profilModel = new ProfilKontenModel();
        $data['item'] = $profilModel->getByJenis('sejarah');
        return view('profil_publik/teks', $data);
    }

    public function visiMisi()
    {
        $profilModel = new ProfilKontenModel();
        $data['item'] = $profilModel->getByJenis('visi_misi');
        return view('profil_publik/teks', $data);
    }

    public function kepalaSekolah()
    {
        $profilModel = new ProfilKontenModel();
        $data['item'] = $profilModel->getByJenis('kepala_sekolah');
        return view('profil_publik/kepala_sekolah', $data);
    }

    public function struktur()
    {
        $organisasiModel = new OrganisasiModel();
        $data['organisasi'] = $organisasiModel->getPublishedWithAnggota();
        return view('profil_publik/struktur', $data);
    }
}