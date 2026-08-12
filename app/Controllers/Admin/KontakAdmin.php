<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PesanKontakModel;

class KontakAdmin extends BaseController
{
    protected $kontakModel;

    public function __construct()
    {
        $this->kontakModel = new PesanKontakModel();
    }

    public function index()
    {
        $data['pesan'] = $this->kontakModel->orderBy('created_at', 'DESC')->findAll();
        return view('admin/kontak/index', $data);
    }

    public function detail($id)
    {
        $pesan = $this->kontakModel->find($id);

        if (!$pesan) {
            return redirect()->to('/admin/kontak')->with('error', 'Pesan tidak ditemukan.');
        }

        if ($pesan['status'] === 'Belum Dibaca') {
            $this->kontakModel->update($id, ['status' => 'Sudah Dibaca']);
            $pesan['status'] = 'Sudah Dibaca';
        }

        $data['pesan'] = $pesan;
        return view('admin/kontak/detail', $data);
    }

    public function delete($id)
    {
        $pesan = $this->kontakModel->find($id);
        if (!$pesan) {
            return redirect()->to('/admin/kontak')->with('error', 'Pesan tidak ditemukan.');
        }

        $this->kontakModel->delete($id);

        return redirect()->to('/admin/kontak')->with('success', 'Pesan berhasil dihapus.');
    }
}   