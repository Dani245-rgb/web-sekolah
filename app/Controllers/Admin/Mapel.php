<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MapelModel;

class Mapel extends BaseController
{
    protected MapelModel $mapelModel;

    public function __construct()
    {
        $this->mapelModel = new MapelModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('cari');

        $builder = $this->mapelModel->orderBy('nama_mapel', 'ASC');
        if ($keyword) {
            $builder->groupStart()
                ->like('nama_mapel', $keyword)
                ->orLike('kode_mapel', $keyword)
                ->groupEnd();
        }

        $data['mapel']   = $builder->paginate(10, 'mapel');
        $data['pager']   = $this->mapelModel->pager;
        $data['keyword'] = $keyword;

        return view('admin/mapel/index', $data);
    }

    public function create()
    {
        return view('admin/mapel/create');
    }

    public function store()
    {
        $rules = [
            'kode_mapel' => 'required|max_length[20]|is_unique[mapel.kode_mapel]',
            'nama_mapel' => 'required|min_length[3]|max_length[100]',
            'kkm'        => 'permit_empty|is_natural',
            'semester'   => 'permit_empty|in_list[Ganjil,Genap]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaMapel = $this->request->getPost('nama_mapel');

        $this->mapelModel->skipValidation(true)->insert([
            'kode_mapel'     => $this->request->getPost('kode_mapel'),
            'nama_mapel'     => $namaMapel,
            'kelompok_mapel' => $this->request->getPost('kelompok_mapel'),
            'kkm'            => $this->request->getPost('kkm') ?: 75,
            'semester'       => $this->request->getPost('semester'),
            'status'         => 'Aktif',
            'ada_nilai'      => $this->request->getPost('ada_nilai') ?: 'Ya',
        ]);

        return redirect()->to('/admin/mapel')->with('success', "Mapel {$namaMapel} berhasil ditambahkan.");
    }

    public function edit($id_mapel)
    {
        $data['mapelData'] = $this->mapelModel->find($id_mapel);

        if (!$data['mapelData']) {
            return redirect()->to('/admin/mapel')->with('errors', ['404' => 'Data mapel tidak ditemukan.']);
        }

        return view('admin/mapel/edit', $data);
    }

    public function update($id_mapel)
    {
        $mapelData = $this->mapelModel->find($id_mapel);
        if (!$mapelData) {
            return redirect()->to('/admin/mapel')->with('errors', ['404' => 'Data mapel tidak ditemukan.']);
        }

        $rules = [
            'kode_mapel' => "required|max_length[20]|is_unique[mapel.kode_mapel,id_mapel,{$id_mapel}]",
            'nama_mapel' => 'required|min_length[3]|max_length[100]',
            'kkm'        => 'permit_empty|is_natural',
            'semester'   => 'permit_empty|in_list[Ganjil,Genap]',
            'status'     => 'required|in_list[Aktif,Nonaktif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $namaMapel = $this->request->getPost('nama_mapel');

        $berhasil = $this->mapelModel->skipValidation(true)->update($id_mapel, [
            'kode_mapel'     => $this->request->getPost('kode_mapel'),
            'nama_mapel'     => $namaMapel,
            'kelompok_mapel' => $this->request->getPost('kelompok_mapel'),
            'kkm'            => $this->request->getPost('kkm') ?: 75,
            'semester'       => $this->request->getPost('semester'),
            'status'         => $this->request->getPost('status'),
            'ada_nilai'      => $this->request->getPost('ada_nilai') ?: 'Ya',
        ]);

        if (!$berhasil) {
            return redirect()->back()->withInput()
                ->with('errors', ['update' => 'Gagal memperbarui data mapel.']);
        }

        return redirect()->to('/admin/mapel')->with('success', "Mapel {$namaMapel} berhasil diperbarui.");
    }

    public function delete($id_mapel)
    {
        $mapelData = $this->mapelModel->find($id_mapel);
        if (!$mapelData) {
            return redirect()->to('/admin/mapel')->with('errors', ['404' => 'Data mapel tidak ditemukan.']);
        }

        if ($this->mapelModel->isDipakaiDiJadwal($id_mapel)) {
            return redirect()->to('/admin/mapel')
                ->with('errors', ['used' => 'Tidak bisa dihapus, mapel ini sudah dipakai di Jadwal Pelajaran.']);
        }

        $this->mapelModel->delete($id_mapel);

        return redirect()->to('/admin/mapel')->with('success', 'Mapel berhasil dihapus.');
    }
}
