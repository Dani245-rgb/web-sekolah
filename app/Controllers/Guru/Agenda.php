<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\AgendaModel;

class Agenda extends BaseController
{
    public function index()
    {
        $agendaModel = new AgendaModel();

        $akanDatang = $agendaModel->getUpcoming(10);

        $sudahLewat = $agendaModel->where('status', 'Published')
            ->where('tanggal <', date('Y-m-d'))
            ->orderBy('tanggal', 'DESC')
            ->findAll(10);

        return view('guru/agenda/index', [
            'akanDatang' => $akanDatang,
            'sudahLewat' => $sudahLewat,
        ]);
    }
}