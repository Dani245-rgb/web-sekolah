<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AgendaModel;

class Agenda extends BaseController
{
    protected AgendaModel $agendaModel;

    public function __construct()
    {
        helper('teks');

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
        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        if (!$this->agendaModel->insert($this->dataDariForm())) {
            return $this->kembaliDenganError($this->agendaModel->errors());
        }

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

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        if (!$this->agendaModel->update($id, $this->dataDariForm())) {
            return $this->kembaliDenganError($this->agendaModel->errors());
        }

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

    private function rules(): array
    {
        return [
            'judul'   => 'required|min_length[3]|max_length[255]',
            'tanggal' => 'required|valid_date',
            'status'  => 'required|in_list[Published,Draft]',
        ];
    }

    /**
     * Data dari form yang sudah dirapikan, dipakai bareng oleh store() dan update().
     */
    private function dataDariForm(): array
    {
        return [
            'judul'   => rapikan_teks((string) $this->request->getPost('judul')),
            'tanggal' => $this->request->getPost('tanggal'),
            'waktu'   => rapikan_teks((string) $this->request->getPost('waktu')),
            'lokasi'  => rapikan_teks((string) $this->request->getPost('lokasi')),
            'status'  => $this->request->getPost('status'),
        ];
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}