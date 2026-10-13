<?php

namespace App\Controllers;

use App\Services\BerandaService;
use App\Services\SeoService;

class Home extends BaseController
{
    protected BerandaService $beranda;
    protected SeoService $seo;

    public function __construct()
    {
        $this->beranda = service('berandaService');
        $this->seo     = service('seoService');
    }

    public function index(): string
    {
        helper('text');

        $data        = $this->beranda->data();
        $data['seo'] = $this->seo->halaman('', '', [
            'path'   => '',
            'jsonld' => [$this->seo->jsonLdSekolah()],
        ]);

        return view('home', $data);
    }
}
