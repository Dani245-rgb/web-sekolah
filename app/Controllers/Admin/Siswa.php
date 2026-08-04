<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\UserModel;
use App\Models\KelasModel;
use App\Models\TahunAjaranModel;
use App\Libraries\ImageCompressor;
use Config\Database;

class Siswa extends BaseController
{
    protected SiswaModel $siswaModel;
    protected UserModel $userModel;
    protected KelasModel $kelasModel;
    protected TahunAjaranModel $tahunAjaranModel;

    public function __construct()
    {
        $this->siswaModel       = new SiswaModel();
        $this->userModel        = new UserModel();
        $this->kelasModel       = new KelasModel();
        $this->tahunAjaranModel = new TahunAjaranModel();
    }

    public function index()
    {
        $keyword    = $this->request->getGet('cari');
        $tahunAktif = $this->tahunAjaranModel->getActive();
        $idTahunAktif = $tahunAktif['id_tahun_ajaran'] ?? 0;

        $builder = $this->siswaModel->getAllWithKelas($idTahunAktif);
        if ($keyword) {
            $builder->groupStart()
                ->like('siswa.nama', $keyword)
                ->orLike('siswa.nis', $keyword)
                ->orLike('siswa.nisn', $keyword)
                ->groupEnd();
        }

        $data['siswa']       = $builder->paginate(50, 'siswa');
        $data['pager']       = $this->siswaModel->pager;
        $data['keyword']     = $keyword;
        $data['tahunAktif']  = $tahunAktif;

        return view('admin/siswa/index', $data);
    }

    public function create()
    {
        $tahunAktif = $this->tahunAjaranModel->getActive();

        if (!$tahunAktif) {
            return redirect()->to('/admin/siswa')
                ->with('errors', ['tahun' => 'Belum ada Tahun Ajaran yang berstatus Aktif. Aktifkan salah satu dulu di menu Tahun Ajaran.']);
        }

        $data['kelas']      = $this->kelasModel->where('status', 'Aktif')
            ->where('id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
            ->orderBy('nama_kelas', 'ASC')->findAll();
        $data['tahunAktif'] = $tahunAktif;

        return view('admin/siswa/create', $data);
    }

    public function store()
    {
        $rules = [
            'nis'            => 'required|max_length[20]|is_unique[siswa.nis]',
            'nisn'           => 'required|max_length[20]|is_unique[siswa.nisn]',
            'nama'           => 'required|min_length[3]|max_length[100]',
            'tanggal_lahir'  => 'required|valid_date',
            'jenis_kelamin'  => 'required|in_list[L,P]',
            'id_kelas'       => 'required|is_natural_no_zero',
            'email'          => 'permit_empty|valid_email',
            'foto'           => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tahunAktif = $this->tahunAjaranModel->getActive();
        if (!$tahunAktif) {
            return redirect()->back()->withInput()
                ->with('errors', ['tahun' => 'Tidak ada Tahun Ajaran Aktif.']);
        }

        $nis           = $this->request->getPost('nis');
        $tanggalLahir  = $this->request->getPost('tanggal_lahir'); // format: YYYY-MM-DD
        $passwordAwal  = date('dmY', strtotime($tanggalLahir)); // ddmmyyyy

        $fotoName = null;
        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            $fotoName = $fotoFile->getRandomName();
            $compressor = new ImageCompressor();
            $compressor->compressAndSave($fotoFile->getTempName(), FCPATH . 'uploads/siswa/' . $fotoName);
        }

        $db = Database::connect();
        $db->transStart();

        $userId = $this->userModel->insert([
            'username' => $nis,
            'password' => password_hash($passwordAwal, PASSWORD_DEFAULT),
            'role_id'  => 3,
            'status'   => 'Aktif',
        ]);

        $idSiswa = $this->siswaModel->skipValidation(true)->insert([
            'user_id'        => $userId,
            'nis'            => $nis,
            'nisn'           => $this->request->getPost('nisn'),
            'nama'           => $this->request->getPost('nama'),
            'tempat_lahir'   => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'  => $tanggalLahir,
            'jenis_kelamin'  => $this->request->getPost('jenis_kelamin'),
            'agama'          => $this->request->getPost('agama'),
            'alamat'         => $this->request->getPost('alamat'),
            'nama_ayah'      => $this->request->getPost('nama_ayah'),
            'nama_ibu'       => $this->request->getPost('nama_ibu'),
            'pekerjaan_ortu' => $this->request->getPost('pekerjaan_ortu'),
            'no_hp_ortu'     => $this->request->getPost('no_hp_ortu'),
            'email'          => $this->request->getPost('email'),
            'foto'           => $fotoName,
            'status'         => 'Aktif',
        ]);

       $sudahAda = $db->table('kelas_siswa')
            ->where('id_siswa', $idSiswa)
            ->where('id_tahun_ajaran', $tahunAktif['id_tahun_ajaran'])
            ->countAllResults();

        if ($sudahAda === 0) {
            $db->table('kelas_siswa')->insert([
                'id_kelas'        => $this->request->getPost('id_kelas'),
                'id_siswa'        => $idSiswa,
                'id_tahun_ajaran' => $tahunAktif['id_tahun_ajaran'],
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('errors', ['db' => 'Gagal menyimpan data siswa.']);
        }

        return redirect()->to('/admin/siswa')
            ->with('success', "Siswa {$this->request->getPost('nama')} berhasil ditambahkan. Username: {$nis}, Password awal: {$passwordAwal}");
    }

    public function edit($id_siswa)
    {
        $data['siswaData'] = $this->siswaModel->find($id_siswa);

        if (!$data['siswaData']) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

        return view('admin/siswa/edit', $data);
    }

    public function update($id_siswa)
    {
        $siswaData = $this->siswaModel->find($id_siswa);
        if (!$siswaData) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

        $rules = [
            'nis'           => "required|max_length[20]|is_unique[siswa.nis,id_siswa,{$id_siswa}]",
            'nisn'          => "required|max_length[20]|is_unique[siswa.nisn,id_siswa,{$id_siswa}]",
            'nama'          => 'required|min_length[3]|max_length[100]',
            'jenis_kelamin' => 'required|in_list[L,P]',
            'email'         => 'permit_empty|valid_email',
            'foto'          => 'permit_empty|is_image[foto]|max_size[foto,2048]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dataUpdate = [
            'nis'            => $this->request->getPost('nis'),
            'nisn'           => $this->request->getPost('nisn'),
            'nama'           => $this->request->getPost('nama'),
            'tempat_lahir'   => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'  => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin'  => $this->request->getPost('jenis_kelamin'),
            'agama'          => $this->request->getPost('agama'),
            'alamat'         => $this->request->getPost('alamat'),
            'nama_ayah'      => $this->request->getPost('nama_ayah'),
            'nama_ibu'       => $this->request->getPost('nama_ibu'),
            'pekerjaan_ortu' => $this->request->getPost('pekerjaan_ortu'),
            'no_hp_ortu'     => $this->request->getPost('no_hp_ortu'),
            'email'          => $this->request->getPost('email'),
        ];

        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            if (!empty($siswaData['foto']) && file_exists(FCPATH . 'uploads/siswa/' . $siswaData['foto'])) {
                unlink(FCPATH . 'uploads/siswa/' . $siswaData['foto']);
            }
            $fotoName = $fotoFile->getRandomName();
            $compressor = new ImageCompressor();
            $compressor->compressAndSave($fotoFile->getTempName(), FCPATH . 'uploads/siswa/' . $fotoName);
            $dataUpdate['foto'] = $fotoName;
        }

        // Sinkronkan username akun login kalau NIS diubah
        $this->userModel->update($siswaData['user_id'], ['username' => $dataUpdate['nis']]);

        $berhasil = $this->siswaModel->skipValidation(true)->update($id_siswa, $dataUpdate);

        if (!$berhasil) {
            return redirect()->back()->withInput()
                ->with('errors', ['update' => 'Gagal memperbarui data siswa.']);
        }

        return redirect()->to('/admin/siswa')->with('success', "Data siswa {$dataUpdate['nama']} berhasil diperbarui.");
    }

    public function delete($id_siswa)
    {
        $siswaData = $this->siswaModel->find($id_siswa);
        if (!$siswaData) {
            return redirect()->to('/admin/siswa')->with('errors', ['404' => 'Data siswa tidak ditemukan.']);
        }

        if ($this->siswaModel->isDipakaiDiModulLain($id_siswa)) {
            return redirect()->to('/admin/siswa')
                ->with('errors', ['used' => 'Tidak bisa dihapus, siswa ini sudah memiliki data akademik.']);
        }

        $db = Database::connect();
        $db->transStart();

        $db->table('kelas_siswa')->where('id_siswa', $id_siswa)->delete();
        $this->siswaModel->delete($id_siswa);
        $this->userModel->delete($siswaData['user_id']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('/admin/siswa')->with('errors', ['db' => 'Gagal menghapus data siswa.']);
        }

        if (!empty($siswaData['foto']) && file_exists(FCPATH . 'uploads/siswa/' . $siswaData['foto'])) {
            unlink(FCPATH . 'uploads/siswa/' . $siswaData['foto']);
        }

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function resetPassword($id)
{
    $siswa = $this->siswaModel->find($id);
    if (!$siswa) {
        return redirect()->to('/admin/siswa')->with('error', 'Siswa tidak ditemukan.');
    }

    $passwordBaru = date('dmY', strtotime($siswa['tanggal_lahir']));

    $userModel = new \App\Models\UserModel();
    $userModel->update($siswa['user_id'], [
        'password'             => password_hash($passwordBaru, PASSWORD_DEFAULT),
        'must_change_password' => true,
        'login_attempts'       => 0,
        'locked_until'         => null,
    ]);

    $auditLogModel = new \App\Models\AuditLogModel();
    $auditLogModel->catat(
        session()->get('id_user'),
        session()->get('username'),
        'reset_password_admin',
        "Reset password siswa {$siswa['nama']} (NIS {$siswa['nis']})."
    );

    return redirect()->to('/admin/siswa')
        ->with('success', "Password {$siswa['nama']} berhasil direset ke tanggal lahir ({$passwordBaru}). Siswa wajib ganti password saat login berikutnya.");
}
}