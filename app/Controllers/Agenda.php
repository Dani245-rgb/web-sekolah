<?php

namespace App\Controllers;

use App\Models\AgendaModel;

class Agenda extends BaseController
{
    protected AgendaModel $agendaModel;

    public function __construct()
    {
        $this->agendaModel = new AgendaModel();
    }

    public function index()
    {
        $data['agenda'] = $this->agendaModel->getSemuaPublished();

        return view('agenda/index', $data);
    }
}