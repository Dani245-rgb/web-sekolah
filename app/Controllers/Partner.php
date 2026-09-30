<?php

namespace App\Controllers;

use App\Models\PartnerModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Partner extends BaseController
{
    protected PartnerModel $partnerModel;

    public function __construct()
    {
        $this->partnerModel = new PartnerModel();
    }

    public function index()
    {
        $data['partner'] = $this->partnerModel->getSemuaPublished();

        return view('partner/index', $data);
    }

    public function detail($slug)
    {
        $partner = $this->partnerModel->getBySlug($slug);

        if (!$partner) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['partner'] = $partner;

        return view('partner/detail', $data);
    }
}