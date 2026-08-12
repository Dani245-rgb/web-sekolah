<?php

namespace App\Controllers;

use App\Models\AgendaModel;

class Agenda extends BaseController
{
    protected $agendaModel;

    public function __construct()
    {
        $this->agendaModel = new AgendaModel();
    }

    public function index()
    {
        $data['agenda'] = $this->agendaModel
            ->where('status', 'Published')
            ->orderBy('tanggal', 'ASC')
            ->findAll();

        return view('agenda/index', $data);
    }
}