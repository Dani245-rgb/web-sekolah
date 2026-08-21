<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KalenderAkademikModel;

class KalenderAkademik extends BaseController
{
    protected $kalenderModel;

    public function __construct()
    {
        $this->kalenderModel = new KalenderAkademikModel();
    }

    public function index()
    {
        $data['kalender'] = $this->kalenderModel->orderBy('tanggal_mulai', 'ASC')->findAll();
        return view('admin/kalender_akademik/index', $data);
    }

    public function create()
    {
        $data['item'] = null;
        return view('admin/kalender_akademik/form', $data);
    }

    public function store()
    {
        $rules = [
            'kegiatan'        => 'required|min_length[3]|max_length[255]',
            'tanggal_mulai'   => 'required|valid_date',
            'tanggal_selesai' => 'permit_empty|valid_date',
            'semester'        => 'required|in_list[Ganjil,Genap]',
            'tahun_ajaran'    => 'required',
            'status'          => 'required|in_list[Published,Draft]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tanggalMulai   = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');

        if (!empty($tanggalSelesai) && strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
            return redirect()->back()->withInput()
                ->with('errors', ['tanggal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        $this->kalenderModel->insert([
            'kegiatan'        => $this->request->getPost('kegiatan'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'keterangan'      => $this->request->getPost('keterangan'),
            'semester'        => $this->request->getPost('semester'),
            'tahun_ajaran'    => $this->request->getPost('tahun_ajaran'),
            'status'          => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/kalender-akademik')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['item'] = $this->kalenderModel->find($id);

        if (!$data['item']) {
            return redirect()->to('/admin/kalender-akademik')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/kalender_akademik/form', $data);
    }

    public function update($id)
    {
        $item = $this->kalenderModel->find($id);
        if (!$item) {
            return redirect()->to('/admin/kalender-akademik')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'kegiatan'        => 'required|min_length[3]|max_length[255]',
            'tanggal_mulai'   => 'required|valid_date',
            'tanggal_selesai' => 'permit_empty|valid_date',
            'semester'        => 'required|in_list[Ganjil,Genap]',
            'tahun_ajaran'    => 'required',
            'status'          => 'required|in_list[Published,Draft]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tanggalMulai   = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');

        if (!empty($tanggalSelesai) && strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
            return redirect()->back()->withInput()
                ->with('errors', ['tanggal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        $this->kalenderModel->update($id, [
            'kegiatan'        => $this->request->getPost('kegiatan'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai'),
            'keterangan'      => $this->request->getPost('keterangan'),
            'semester'        => $this->request->getPost('semester'),
            'tahun_ajaran'    => $this->request->getPost('tahun_ajaran'),
            'status'          => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/kalender-akademik')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->kalenderModel->find($id);
        if (!$item) {
            return redirect()->to('/admin/kalender-akademik')->with('error', 'Data tidak ditemukan.');
        }

        $this->kalenderModel->delete($id);

        return redirect()->to('/admin/kalender-akademik')->with('success', 'Kegiatan berhasil dihapus.');
    }
}