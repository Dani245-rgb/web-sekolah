<?php

namespace App\Controllers;

use App\Models\PartnerModel;

class Partner extends BaseController
{
    protected $partnerModel;

    public function __construct()
    {
        $this->partnerModel = new PartnerModel();
    }

    public function index()
    {
        $data['partner'] = $this->partnerModel->where('status', 'Published')->orderBy('nama', 'ASC')->findAll();
        return view('partner/index', $data);
    }

    public function detail($slug)
    {
        $partner = $this->partnerModel->getBySlug($slug);

        if (!$partner) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data['partner'] = $partner;
        return view('partner/detail', $data);
    }
}