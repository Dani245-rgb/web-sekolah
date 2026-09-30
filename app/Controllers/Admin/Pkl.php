<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\PklPenempatanModel;
use App\Models\SiswaModel;

class Pkl extends BaseController
{
    protected PklPenempatanModel $pklModel;
    protected SiswaModel $siswaModel;
    protected GuruModel $guruModel;

    public function __construct()
    {
        helper('teks');

        $this->pklModel   = new PklPenempatanModel();
        $this->siswaModel = new SiswaModel();
        $this->guruModel  = new GuruModel();
    }

    public function index()
    {
        $keyword = $this->request->getGet('keyword');
        $status  = $this->request->getGet('status');

        $filter = [
            'keyword' => is_string($keyword) && trim($keyword) !== '' ? trim($keyword) : null,
            'status'  => in_array($status, PklPenempatanModel::STATUS, true) ? $status : null,
        ];

        return view('admin/pkl/index', [
            'daftar' => $this->pklModel->getAllWithRelasi($filter),
            'filter' => $filter,
        ]);
    }

    public function create()
    {
        return view('admin/pkl/form', $this->dataForm(null));
    }

    public function store()
    {
        $data = $this->dataDariForm();

        $masalah = $this->cariMasalah($data);
        if ($masalah) {
            return redirect()->back()->withInput()->with('errors', $masalah);
        }

        if (!$this->pklModel->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->pklModel->errors());
        }

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $penempatan = $this->pklModel->find($id);
        if (!$penempatan) {
            return $this->tidakDitemukan();
        }

        return view('admin/pkl/form', $this->dataForm($penempatan));
    }

    public function update($id)
    {
        $penempatan = $this->pklModel->find($id);
        if (!$penempatan) {
            return $this->tidakDitemukan();
        }

        $data = $this->dataDariForm();

        $masalah = $this->cariMasalah($data, (int) $id);
        if ($masalah) {
            return redirect()->back()->withInput()->with('errors', $masalah);
        }

        if (!$this->pklModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->pklModel->errors());
        }

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (!$this->pklModel->find($id)) {
            return $this->tidakDitemukan();
        }

        $this->pklModel->delete($id); // jurnal ikut terhapus (ON DELETE CASCADE)

        return redirect()->to('/admin/pkl')->with('success', 'Penempatan PKL berhasil dihapus.');
    }

    /**
     * Data dari form yang sudah dirapikan, dipakai bareng oleh store() dan update().
     */
    private function dataDariForm(): array
    {
        return [
            'id_siswa'                  => $this->request->getPost('id_siswa'),
            'id_guru_pembimbing'        => $this->request->getPost('id_guru_pembimbing') ?: null,
            'nama_perusahaan'           => rapikan_teks((string) $this->request->getPost('nama_perusahaan')),
            'alamat_perusahaan'         => $this->request->getPost('alamat_perusahaan'),
            'nama_pembimbing_industri'  => rapikan_teks((string) $this->request->getPost('nama_pembimbing_industri')),
            'no_hp_pembimbing_industri' => rapikan_teks((string) $this->request->getPost('no_hp_pembimbing_industri')),
            'tanggal_mulai'             => $this->request->getPost('tanggal_mulai'),
            'tanggal_selesai'           => $this->request->getPost('tanggal_selesai') ?: null,
            'status'                    => $this->request->getPost('status'),
            'catatan'                   => $this->request->getPost('catatan'),
        ];
    }

    /**
     * Validasi biasa dulu, lalu aturan bisnis. Return daftar error (kosong = lolos).
     */
    private function cariMasalah(array $data, ?int $kecualiId = null): array
    {
        if (!$this->pklModel->validate($data)) {
            return $this->pklModel->errors();
        }

        $pesan = $this->pklModel->cekAturan($data, $kecualiId);

        return $pesan !== null ? ['aturan' => $pesan] : [];
    }

    private function dataForm(?array $penempatan): array
    {
        return [
            'siswaList'  => $this->siswaModel->orderBy('nama', 'ASC')->findAll(),
            'guruList'   => $this->guruModel->where('status', 'Aktif')->orderBy('nama', 'ASC')->findAll(),
            'penempatan' => $penempatan,
        ];
    }

    private function tidakDitemukan()
    {
        return redirect()->to('/admin/pkl')->with('errors', ['404' => 'Data penempatan tidak ditemukan.']);
    }
}