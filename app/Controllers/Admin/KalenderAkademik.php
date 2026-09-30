<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KalenderAkademikModel;

class KalenderAkademik extends BaseController
{
    protected KalenderAkademikModel $kalenderModel;

    public function __construct()
    {
        helper('teks');

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
        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $data = $this->dataDariForm();

        if ($this->urutanTanggalSalah($data)) {
            return $this->kembaliDenganError(['tanggal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        if (!$this->kalenderModel->insert($data)) {
            return $this->kembaliDenganError($this->kalenderModel->errors());
        }

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

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $data = $this->dataDariForm();

        if ($this->urutanTanggalSalah($data)) {
            return $this->kembaliDenganError(['tanggal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        if (!$this->kalenderModel->update($id, $data)) {
            return $this->kembaliDenganError($this->kalenderModel->errors());
        }

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

    private function rules(): array
    {
        return [
            'kegiatan'        => 'required|min_length[3]|max_length[255]',
            'tanggal_mulai'   => 'required|valid_date',
            'tanggal_selesai' => 'permit_empty|valid_date',
            'semester'        => 'required|in_list[Ganjil,Genap]',
            'tahun_ajaran'    => 'required',
            'status'          => 'required|in_list[Published,Draft]',
        ];
    }

    /**
     * Data dari form yang sudah dirapikan, dipakai bareng oleh store() dan update().
     * tanggal_selesai kosong disimpan sebagai NULL, bukan string kosong.
     */
    private function dataDariForm(): array
    {
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');

        return [
            'kegiatan'        => rapikan_teks((string) $this->request->getPost('kegiatan')),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai' => ($tanggalSelesai !== null && $tanggalSelesai !== '') ? $tanggalSelesai : null,
            'keterangan'      => $this->request->getPost('keterangan'),
            'semester'        => $this->request->getPost('semester'),
            'tahun_ajaran'    => rapikan_teks((string) $this->request->getPost('tahun_ajaran')),
            'status'          => $this->request->getPost('status'),
        ];
    }

    private function urutanTanggalSalah(array $data): bool
    {
        return $data['tanggal_selesai'] !== null
            && strtotime($data['tanggal_selesai']) < strtotime($data['tanggal_mulai']);
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}