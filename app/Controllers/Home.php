<?php

namespace App\Controllers;

use App\Services\BerandaService;

class Home extends BaseController
{
    protected BerandaService $beranda;

    public function __construct()
    {
        $this->beranda = service('berandaService');
    }

    public function index(): string
    {
        helper('text');

        return view('home', $this->beranda->data());
    }
}