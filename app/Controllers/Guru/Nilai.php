<?php

namespace App\Controllers\Guru;

use App\Controllers\BaseController;
use App\Models\GuruModel;
use App\Models\JadwalModel;
use App\Models\KelasSiswaModel;
use App\Models\TahunAjaranModel;
use App\Models\PengaturanNilaiModel;
use App\Models\KomponenNilaiModel;
use App\Models\NilaiSiswaModel;
use App\Models\SemesterModel;
use App\Models\KategoriNilaiModel;

class Nilai extends BaseController
{
    protected $pengaturanModel;
    protected $komponenModel;
    protected $nilaiModel;
    protected $semesterModel;
    protected $kategoriModel;

    public function __construct()
    {
        $this->pengaturanModel = new PengaturanNilaiModel();
        $this->komponenModel   = new KomponenNilaiModel();
        $this->nilaiModel      = new NilaiSiswaModel();
        $this->semesterModel   = new SemesterModel();
        $this->kategoriModel   = new KategoriNilaiModel();
    }

    protected function getGuruLogin()
    {
        $userId = session()->get('id_user');
        $guruModel = new GuruModel();
        return $guruModel->where('user_id', $userId)->first();
    }

    protected function getJadwalMilikSaya($idJadwal, $guru)
    {
        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return null;
        }

        return $jadwal;
    }

   /**
     * GET /guru/nilai/form/(:num)/pengaturan
     * Selalu tampilkan form pengaturan kategori/komponen, dipakai guru
     * untuk MENAMBAH atau MENGUBAH komponen nilai yang sudah ada
     * (beda dari form() yang cuma nampilin ini kalau pengaturan masih kosong).
     */
    public function pengaturanManual($idJadwal)
    {
        $guru = $this->getGuruLogin();
        $jadwal = $this->getJadwalMilikSaya($idJadwal, $guru);

        if (!$jadwal) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $semesterAktif = $this->semesterModel->getActive();

        if (!$semesterAktif) {
            return redirect()->to('/guru/dashboard')->with('errors', ['semester' => 'Belum ada semester aktif.']);
        }

        $pengaturan = $this->pengaturanModel->getPengaturan(
            (int) $guru['id_guru'],
            (int) $jadwal['id_kelas'],
            (int) $jadwal['id_mapel'],
            (int) $semesterAktif['id_semester']
        );

        // Ambil kategori & komponen yang sudah ada (kalau ada), supaya guru
        // bisa lihat & edit yang lama, bukan mulai dari kosong lagi.
        $kategoriList = [];
        if ($pengaturan) {
            $kategoriList = $this->kategoriModel->getByPengaturan($pengaturan['id_pengaturan']);
            foreach ($kategoriList as &$kat) {
                $kat['komponen'] = $this->komponenModel
                    ->where('id_kategori', $kat['id_kategori'])
                    ->orderBy('urutan', 'ASC')
                    ->findAll();
            }
            unset($kat);
        }

        return view('guru/nilai/pengaturan', [
            'jadwal'        => $jadwal,
            'semesterAktif' => $semesterAktif,
            'pengaturan'    => $pengaturan,
            'kategoriList'  => $kategoriList,
        ]);
    }

   public function form($idJadwal)
    {
        $guru = $this->getGuruLogin();
        $jadwal = $this->getJadwalMilikSaya($idJadwal, $guru);

        if (!$jadwal) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $semesterAktif = $this->semesterModel->getActive();

        if (!$semesterAktif) {
            return redirect()->to('/guru/dashboard')->with('errors', ['semester' => 'Belum ada semester aktif.']);
        }

        $pengaturan = $this->pengaturanModel->getPengaturan(
            (int) $guru['id_guru'],
            (int) $jadwal['id_kelas'],
            (int) $jadwal['id_mapel'],
            (int) $semesterAktif['id_semester']
        );

        if (!$pengaturan) {
            return view('guru/nilai/pengaturan', [
                'jadwal'        => $jadwal,
                'semesterAktif' => $semesterAktif,
                'komponenList'  => [],
            ]);
        }

        $komponenList = $this->komponenModel->where('id_pengaturan', $pengaturan['id_pengaturan'])
            ->orderBy('urutan', 'ASC')->findAll();

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        $kelasSiswaModel = new KelasSiswaModel();
        $siswaList = $tahunAktif
            ? $kelasSiswaModel->getSiswaByKelas($jadwal['id_kelas'], $tahunAktif['id_tahun_ajaran'])
            : [];

        $idKomponenList = array_column($komponenList, 'id_komponen');
        $nilaiRows = $idKomponenList ? $this->nilaiModel->whereIn('id_komponen', $idKomponenList)->findAll() : [];

        $nilaiMap = [];
        foreach ($nilaiRows as $row) {
            $nilaiMap[$row['id_siswa']][$row['id_komponen']] = $row['nilai'];
        }

        return view('guru/nilai/input', [
            'jadwal'       => $jadwal,
            'pengaturan'   => $pengaturan,
            'komponenList' => $komponenList,
            'siswaList'    => $siswaList,
            'nilaiMap'     => $nilaiMap,
        ]);
    }
    
    /**
     * v1.0: sekarang juga nangkep & simpen link_referensi per komponen (opsional, boleh kosong).
     */
    /**
     * v2.0: sekarang komponen dikelompokkan dalam Kategori.
     * Bobot dicek 2 level: total bobot antar-kategori harus 100%,
     * dan total bobot komponen di dalam tiap kategori juga harus 100%.
     */
    public function simpanPengaturan($idJadwal)
    {
        $guru = $this->getGuruLogin();
        $jadwal = $this->getJadwalMilikSaya($idJadwal, $guru);

        if (!$jadwal) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $semesterAktif = $this->semesterModel->getActive();

        $kkm          = (int) $this->request->getPost('kkm');
        $kategoriData = $this->request->getPost('kategori');

        if (empty($kategoriData) || !is_array($kategoriData)) {
            return redirect()->back()->withInput()->with('errors', ['kosong' => 'Kategori & komponen nilai wajib diisi.']);
        }

        $totalBobotKategori = 0;

        foreach ($kategoriData as $kat) {
            if (empty($kat['nama']) || $kat['bobot'] === '' || empty($kat['komponen'])) {
                return redirect()->back()->withInput()->with('errors', ['kosong' => 'Setiap kategori wajib punya nama, bobot, dan minimal 1 komponen.']);
            }

            $totalBobotKategori += (float) $kat['bobot'];

            $totalBobotKomponen = 0;
            foreach ($kat['komponen'] as $komp) {
                if (empty($komp['nama']) || $komp['bobot'] === '') {
                    return redirect()->back()->withInput()->with('errors', ['kosong' => 'Setiap komponen wajib punya nama & bobot.']);
                }
                $totalBobotKomponen += (float) $komp['bobot'];
            }

            if ($totalBobotKomponen != 100) {
                return redirect()->back()->withInput()->with('errors', [
                    'bobot' => "Total bobot komponen dalam kategori \"{$kat['nama']}\" harus 100%, saat ini {$totalBobotKomponen}%."
                ]);
            }
        }

        if ($totalBobotKategori != 100) {
            return redirect()->back()->withInput()->with('errors', [
                'bobot' => "Total bobot kategori harus 100%, saat ini {$totalBobotKategori}%."
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $existing = $this->pengaturanModel->getPengaturan(
            (int) $guru['id_guru'],
            (int) $jadwal['id_kelas'],
            (int) $jadwal['id_mapel'],
            (int) $semesterAktif['id_semester']
        );

        if ($existing) {
            $idPengaturan = $existing['id_pengaturan'];
            $this->pengaturanModel->update($idPengaturan, ['kkm' => $kkm]);
            $this->komponenModel->where('id_pengaturan', $idPengaturan)->delete();
            $this->kategoriModel->where('id_pengaturan', $idPengaturan)->delete();
        } else {
            $idPengaturan = $this->pengaturanModel->insert([
                'id_guru'     => $guru['id_guru'],
                'id_kelas'    => $jadwal['id_kelas'],
                'id_mapel'    => $jadwal['id_mapel'],
                'id_semester' => $semesterAktif['id_semester'],
                'kkm'         => $kkm,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $urutan = 0;
        foreach ($kategoriData as $kat) {
            $idKategori = $this->kategoriModel->insert([
                'id_pengaturan' => $idPengaturan,
                'nama_kategori' => $kat['nama'],
                'bobot'         => $kat['bobot'],
                'created_at'    => date('Y-m-d H:i:s'),
            ]);

            foreach ($kat['komponen'] as $komp) {
                $this->komponenModel->insert([
                    'id_pengaturan'  => $idPengaturan,
                    'id_kategori'    => $idKategori,
                    'nama_komponen'  => $komp['nama'],
                    'link_referensi' => $komp['link'] ?? null,
                    'bobot'          => $komp['bobot'],
                    'urutan'         => $urutan++,
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, pengaturan tidak tersimpan.']);
        }

        return redirect()->to('/guru/nilai/form/' . $idJadwal)
            ->with('success', 'Pengaturan kategori, komponen & bobot nilai tersimpan.');
    }

    public function simpan($idJadwal)
    {
        $guru = $this->getGuruLogin();
        $jadwal = $this->getJadwalMilikSaya($idJadwal, $guru);

        if (!$jadwal) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $nilai = $this->request->getPost('nilai');

        if (empty($nilai) || !is_array($nilai)) {
            return redirect()->back()->with('errors', ['kosong' => 'Tidak ada nilai yang dikirim.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        foreach ($nilai as $id_siswa => $perKomponen) {
            foreach ($perKomponen as $id_komponen => $nilaiValue) {
                if ($nilaiValue === '' || $nilaiValue === null) continue;

                $existing = $this->nilaiModel->where('id_siswa', $id_siswa)
                    ->where('id_komponen', $id_komponen)->first();

                if ($existing) {
                    $this->nilaiModel->update($existing['id_nilai'], ['nilai' => $nilaiValue]);
                } else {
                    $this->nilaiModel->insert([
                        'id_siswa'    => $id_siswa,
                        'id_komponen' => $id_komponen,
                        'nilai'       => $nilaiValue,
                    ]);
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, nilai tidak tersimpan.']);
        }

        return redirect()->to('/guru/nilai/form/' . $idJadwal)->with('success', 'Nilai berhasil disimpan.');
    }

    public function rekap($idJadwal)
    {
        $guru = $this->getGuruLogin();
        $jadwal = $this->getJadwalMilikSaya($idJadwal, $guru);

        if (!$jadwal) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $semesterAktif = $this->semesterModel->getActive();

        $pengaturan = $this->pengaturanModel->getPengaturan(
            (int) $guru['id_guru'],
            (int) $jadwal['id_kelas'],
            (int) $jadwal['id_mapel'],
            (int) $semesterAktif['id_semester']
        );

        if (!$pengaturan) {
            return redirect()->to('/guru/nilai/form/' . $idJadwal)
                ->with('errors', ['belum' => 'Atur komponen & bobot dulu sebelum lihat rekap.']);
        }

        $komponenList = $this->komponenModel->where('id_pengaturan', $pengaturan['id_pengaturan'])->findAll();
        $kategoriList = $this->kategoriModel->getByPengaturan($pengaturan['id_pengaturan']);

        $tahunAjaranModel = new TahunAjaranModel();
        $tahunAktif = $tahunAjaranModel->getActive();

        $kelasSiswaModel = new KelasSiswaModel();
        $siswaList = $tahunAktif
            ? $kelasSiswaModel->getSiswaByKelas($jadwal['id_kelas'], $tahunAktif['id_tahun_ajaran'])
            : [];

        $rekap = $this->hitungRekap($pengaturan, $kategoriList, $komponenList, $siswaList);

        return view('guru/nilai/rekap', [
            'jadwal'     => $jadwal,
            'pengaturan' => $pengaturan,
            'rekap'      => $rekap,
        ]);
    }

    /**
     * v2.0: nilai akhir dihitung 2 tahap sesuai struktur Kategori → Komponen.
     * Tahap 1: nilai per kategori = Σ (nilai komponen × bobot komponen dalam kategori / 100)
     * Tahap 2: nilai akhir       = Σ (nilai kategori × bobot kategori / 100)
     */
    protected function hitungRekap(array $pengaturan, array $kategoriList, array $komponenList, array $siswaList): array
    {
        // Kelompokkan komponen berdasarkan kategorinya masing-masing
        $komponenPerKategori = [];
        foreach ($komponenList as $komp) {
            $komponenPerKategori[$komp['id_kategori']][] = $komp;
        }

        $idKomponenList = array_column($komponenList, 'id_komponen');
        $rows = $idKomponenList ? $this->nilaiModel->whereIn('id_komponen', $idKomponenList)->findAll() : [];

        // nilaiMap[id_siswa][id_komponen] = nilai
        $nilaiMap = [];
        foreach ($rows as $row) {
            $nilaiMap[$row['id_siswa']][$row['id_komponen']] = $row['nilai'];
        }

        $hasil = [];
        foreach ($siswaList as $s) {
            $idSiswa = $s['id_siswa'];
            $nilaiAkhir = 0;

            foreach ($kategoriList as $kat) {
                $komponenDalamKategori = $komponenPerKategori[$kat['id_kategori']] ?? [];

                $nilaiKategori = 0;
                foreach ($komponenDalamKategori as $komp) {
                    $nilaiKomponen = $nilaiMap[$idSiswa][$komp['id_komponen']] ?? 0;
                    $nilaiKategori += $nilaiKomponen * ((float) $komp['bobot'] / 100);
                }

                $nilaiAkhir += $nilaiKategori * ((float) $kat['bobot'] / 100);
            }

            $hasil[] = [
                'nama_siswa'  => $s['nama'],
                'nilai_akhir' => round($nilaiAkhir, 2),
                'status'      => $nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas',
            ];
        }

        return $hasil;
    }
}
