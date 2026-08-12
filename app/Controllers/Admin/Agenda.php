<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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
        $data['agenda'] = $this->agendaModel->orderBy('tanggal', 'DESC')->findAll();
        return view('admin/agenda/index', $data);
    }

    public function create()
    {
        $data['agenda'] = null;
        return view('admin/agenda/form', $data);
    }

    public function store()
    {
        $rules = [
            'judul'   => 'required|min_length[3]|max_length[255]',
            'tanggal' => 'required|valid_date',
            'status'  => 'required|in_list[Published,Draft]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->agendaModel->insert([
            'judul'   => $this->request->getPost('judul'),
            'tanggal' => $this->request->getPost('tanggal'),
            'waktu'   => $this->request->getPost('waktu'),
            'lokasi'  => $this->request->getPost('lokasi'),
            'status'  => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/agenda')->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['agenda'] = $this->agendaModel->find($id);

        if (!$data['agenda']) {
            return redirect()->to('/admin/agenda')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/agenda/form', $data);
    }

    public function update($id)
    {
        $agenda = $this->agendaModel->find($id);
        if (!$agenda) {
            return redirect()->to('/admin/agenda')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'judul'   => 'required|min_length[3]|max_length[255]',
            'tanggal' => 'required|valid_date',
            'status'  => 'required|in_list[Published,Draft]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->agendaModel->update($id, [
            'judul'   => $this->request->getPost('judul'),
            'tanggal' => $this->request->getPost('tanggal'),
            'waktu'   => $this->request->getPost('waktu'),
            'lokasi'  => $this->request->getPost('lokasi'),
            'status'  => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/agenda')->with('success', 'Agenda berhasil diperbarui.');
    }

    public function delete($id)
    {
        $agenda = $this->agendaModel->find($id);
        if (!$agenda) {
            return redirect()->to('/admin/agenda')->with('error', 'Data tidak ditemukan.');
        }

        $this->agendaModel->delete($id);

        return redirect()->to('/admin/agenda')->with('success', 'Agenda berhasil dihapus.');
    }
}