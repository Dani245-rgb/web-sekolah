<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PengumumanModel;
use App\Services\SlugService;

class Pengumuman extends BaseController
{
    protected PengumumanModel $pengumumanModel;
    protected SlugService $slugService;

    public function __construct()
    {
        helper('teks');

        $this->pengumumanModel = new PengumumanModel();
        $this->slugService     = service('slugService');
    }

    public function index()
    {
        $data['pengumuman'] = $this->pengumumanModel->orderBy('tanggal_publish', 'DESC')->findAll();
        return view('admin/pengumuman/index', $data);
    }

    public function create()
    {
        $data['pengumuman'] = null;
        return view('admin/pengumuman/form', $data);
    }

    public function store()
    {
        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $judul = rapikan_teks((string) $this->request->getPost('judul'));

        if ($this->pengumumanModel->judulSudahAda($judul)) {
            return $this->kembaliDenganError(['judul' => 'Judul pengumuman sudah dipakai.']);
        }

        $berhasil = $this->pengumumanModel->insert([
            'judul'           => $judul,
            'slug'            => $this->slugService->buatUnik($judul, 'pengumuman', 'id', null, 'pengumuman'),
            'isi'             => $this->request->getPost('isi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish'),
            'status'          => $this->request->getPost('status'),
        ]);

        if (!$berhasil) {
            return $this->kembaliDenganError($this->pengumumanModel->errors());
        }

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data['pengumuman'] = $this->pengumumanModel->find($id);

        if (!$data['pengumuman']) {
            return redirect()->to('/admin/pengumuman')->with('error', 'Data tidak ditemukan.');
        }

        return view('admin/pengumuman/form', $data);
    }

    public function update($id)
    {
        $pengumuman = $this->pengumumanModel->find($id);
        if (!$pengumuman) {
            return redirect()->to('/admin/pengumuman')->with('error', 'Data tidak ditemukan.');
        }

        if (!$this->validate($this->rules())) {
            return $this->kembaliDenganError($this->validator->getErrors());
        }

        $judul = rapikan_teks((string) $this->request->getPost('judul'));

        if ($this->pengumumanModel->judulSudahAda($judul, (int) $id)) {
            return $this->kembaliDenganError(['judul' => 'Judul pengumuman sudah dipakai.']);
        }

        $berhasil = $this->pengumumanModel->update($id, [
            'judul'           => $judul,
            'slug'            => $this->slugService->buatUnik($judul, 'pengumuman', 'id', (int) $id, 'pengumuman'),
            'isi'             => $this->request->getPost('isi'),
            'tanggal_publish' => $this->request->getPost('tanggal_publish'),
            'status'          => $this->request->getPost('status'),
        ]);

        if (!$berhasil) {
            return $this->kembaliDenganError($this->pengumumanModel->errors());
        }

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function delete($id)
    {
        $pengumuman = $this->pengumumanModel->find($id);
        if (!$pengumuman) {
            return redirect()->to('/admin/pengumuman')->with('error', 'Data tidak ditemukan.');
        }

        $this->pengumumanModel->delete($id);

        return redirect()->to('/admin/pengumuman')->with('success', 'Pengumuman berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'judul'           => 'required|min_length[3]|max_length[255]',
            'isi'             => 'required',
            'tanggal_publish' => 'required|valid_date',
            'status'          => 'required|in_list[Published,Draft]',
        ];
    }

    private function kembaliDenganError(array $errors)
    {
        return redirect()->back()->withInput()->with('errors', $errors);
    }
}