<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KelasModel;
use App\Models\GuruModel;
use App\Models\TahunAjaranModel;

class Kelas extends BaseController
{
    protected KelasModel $kelasModel;
    protected GuruModel $guruModel;
    protected TahunAjaranModel $tahunAjaranModel;

    public function __construct()
    {
        $this->kelasModel       = new KelasModel();
        $this->guruModel        = new GuruModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('cari');

        $builder = $this->kelasModel->getAllWithRelasi();
        if ($keyword) {
            $builder->groupStart()
                    ->like('kelas.nama_kelas', $keyword)
                    ->orLike('kelas.jurusan', $keyword)
                    ->groupEnd();
        }

        $kelas = $builder->paginate(10, 'kelas');

        // Tambahkan jumlah siswa per baris (sementara selalu 0)
        foreach ($kelas as &$k) {
            $k['jumlah_siswa'] = $this->kelasModel->countSiswa($k['id_kelas']);
        }

        $data['kelas']   = $kelas;
        $data['pager']   = $this->kelasModel->pager;
        $data['keyword'] = $keyword;

        return view('admin/kelas/index', $data);
    }

    public function create()
    {
        $data['guru']        = $this->guruModel->where('status', 'Aktif')->orderBy('nama', 'ASC')->findAll();
        $data['tahunAjaran'] = $this->tahunAjaranModel->getForDropdown();

        return view('admin/kelas/create', $data);
    }

    public function store()
    {
        $rules = [
            'tingkat'         => 'required|in_list[X,XI,XII]',
            'jurusan'         => 'required|max_length[50]',
            'rombel'          => 'required|is_natural_no_zero',
            'id_tahun_ajaran' => 'required|is_natural_no_zero',
            'kapasitas'       => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tingkat       = $this->request->getPost('tingkat');
        $jurusan       = $this->request->getPost('jurusan');
        $rombel        = (int) $this->request->getPost('rombel');
        $idTahunAjaran = (int) $this->request->getPost('id_tahun_ajaran');

        // Cegah kelas duplikat di tahun ajaran yang sama
        if ($this->kelasModel->isDuplikat($tingkat, $jurusan, $rombel, $idTahunAjaran)) {
            return redirect()->back()->withInput()
                ->with('errors', ['duplikat' => "Kelas {$tingkat} {$jurusan} {$rombel} sudah ada di tahun ajaran ini."]);
        }

        // Auto-generate nama kelas
        $namaKelas = trim("{$tingkat} {$jurusan} {$rombel}");

        $this->kelasModel->insert([
            'tingkat'         => $tingkat,
            'jurusan'         => $jurusan,
            'rombel'          => $rombel,
            'nama_kelas'      => $namaKelas,
            'wali_kelas_id'   => $this->request->getPost('wali_kelas_id') ?: null,
            'ruangan'         => $this->request->getPost('ruangan'),
            'kapasitas'       => $this->request->getPost('kapasitas') ?: 36,
            'id_tahun_ajaran' => $idTahunAjaran,
            'status'          => 'Aktif',
        ]);

        return redirect()->to('/admin/kelas')->with('success', "Kelas {$namaKelas} berhasil ditambahkan.");
    }

    public function edit($id_kelas)
    {
        $data['kelasData']   = $this->kelasModel->find($id_kelas);
        $data['guru']        = $this->guruModel->where('status', 'Aktif')->orderBy('nama', 'ASC')->findAll();
        $data['tahunAjaran'] = $this->tahunAjaranModel->getForDropdown();

        if (!$data['kelasData']) {
            return redirect()->to('/admin/kelas')->with('errors', ['404' => 'Data kelas tidak ditemukan.']);
        }

        return view('admin/kelas/edit', $data);
    }

    public function update($id_kelas)
    {
        $kelasData = $this->kelasModel->find($id_kelas);
        if (!$kelasData) {
            return redirect()->to('/admin/kelas')->with('errors', ['404' => 'Data kelas tidak ditemukan.']);
        }

        $rules = [
            'tingkat'         => 'required|in_list[X,XI,XII]',
            'jurusan'         => 'required|max_length[50]',
            'rombel'          => 'required|is_natural_no_zero',
            'id_tahun_ajaran' => 'required|is_natural_no_zero',
            'kapasitas'       => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tingkat       = $this->request->getPost('tingkat');
        $jurusan       = $this->request->getPost('jurusan');
        $rombel        = (int) $this->request->getPost('rombel');
        $idTahunAjaran = (int) $this->request->getPost('id_tahun_ajaran');

        if ($this->kelasModel->isDuplikat($tingkat, $jurusan, $rombel, $idTahunAjaran, (int) $id_kelas)) {
            return redirect()->back()->withInput()
                ->with('errors', ['duplikat' => "Kelas {$tingkat} {$jurusan} {$rombel} sudah ada di tahun ajaran ini."]);
        }

        $namaKelas = trim("{$tingkat} {$jurusan} {$rombel}");

        $this->kelasModel->update($id_kelas, [
            'tingkat'         => $tingkat,
            'jurusan'         => $jurusan,
            'rombel'          => $rombel,
            'nama_kelas'      => $namaKelas,
            'wali_kelas_id'   => $this->request->getPost('wali_kelas_id') ?: null,
            'ruangan'         => $this->request->getPost('ruangan'),
            'kapasitas'       => $this->request->getPost('kapasitas') ?: 36,
            'id_tahun_ajaran' => $idTahunAjaran,
            'status'          => $this->request->getPost('status'),
        ]);

        return redirect()->to('/admin/kelas')->with('success', "Kelas {$namaKelas} berhasil diperbarui.");
    }

    public function delete($id_kelas)
    {
        $kelasData = $this->kelasModel->find($id_kelas);
        if (!$kelasData) {
            return redirect()->to('/admin/kelas')->with('errors', ['404' => 'Data kelas tidak ditemukan.']);
        }

        // Cegah hapus kalau masih ada siswa (nanti aktif begitu modul Siswa/pivot kelas_siswa selesai)
        if ($this->kelasModel->countSiswa($id_kelas) > 0) {
            return redirect()->to('/admin/kelas')
                ->with('errors', ['used' => 'Tidak bisa dihapus, kelas ini masih memiliki siswa.']);
        }

        $this->kelasModel->delete($id_kelas);

        return redirect()->to('/admin/kelas')->with('success', 'Kelas berhasil dihapus.');
    }
}