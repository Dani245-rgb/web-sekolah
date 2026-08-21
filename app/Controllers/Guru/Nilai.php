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
use App\Models\AuditLogModel;
use App\Models\SiswaModel;
use App\Libraries\WhatsappService;

class Nilai extends BaseController
{
    protected $pengaturanModel;
    protected $komponenModel;
    protected $nilaiModel;
    protected $semesterModel;
    protected $kategoriModel;
    protected $auditLogModel;

    public function __construct()
    {
        $this->pengaturanModel = new PengaturanNilaiModel();
        $this->komponenModel   = new KomponenNilaiModel();
        $this->nilaiModel      = new NilaiSiswaModel();
        $this->semesterModel   = new SemesterModel();
        $this->kategoriModel   = new KategoriNilaiModel();
        $this->auditLogModel   = new AuditLogModel();
    }

    /**
     * Ambil data guru yang sedang login.
     * Melempar RuntimeException kalau data guru tidak ditemukan
     * (misal akun user ada tapi data guru sudah terhapus).
     */
    protected function getGuruLogin()
    {
        $userId    = session()->get('id_user');
        $guruModel = new GuruModel();
        $guru      = $guruModel->where('user_id', $userId)->first();

        if (!$guru) {
            throw new \RuntimeException('DATA_GURU_TIDAK_DITEMUKAN');
        }

        return $guru;
    }

    /**
     * Redirect standar kalau data guru tidak ditemukan.
     */
    protected function redirectGuruTidakDitemukan()
    {
        return redirect()->to('/logout')
            ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
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
     */
    public function pengaturanManual($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

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
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

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

        $kategoriList = $this->kategoriModel->getByPengaturan($pengaturan['id_pengaturan']);

        $komponenPerKategori = [];
        foreach ($komponenList as $komp) {
            $komponenPerKategori[$komp['id_kategori']][] = $komp;
        }

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
            'jadwal'              => $jadwal,
            'pengaturan'          => $pengaturan,
            'komponenList'        => $komponenList,
            'kategoriList'        => $kategoriList,
            'komponenPerKategori' => $komponenPerKategori,
            'siswaList'           => $siswaList,
            'nilaiMap'            => $nilaiMap,
        ]);
    }

    public function simpanPengaturan($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

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

        foreach ($kategoriData as $kat) {
            if (empty($kat['nama']) || $kat['bobot'] === '' || empty($kat['komponen'])) {
                return redirect()->back()->withInput()->with('errors', ['kosong' => 'Setiap kategori wajib punya nama, bobot, dan minimal 1 komponen.']);
            }

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
                    'keterangan'     => $komp['keterangan'] ?? null,
                    'bobot'          => $komp['bobot'],
                    'urutan'         => $urutan++,
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, pengaturan tidak tersimpan.']);
        }

        $daftarKategoriStr = [];
        foreach ($kategoriData as $kat) {
            $namaKomponen = array_column($kat['komponen'], 'nama');
            $daftarKategoriStr[] = "{$kat['nama']} (bobot {$kat['bobot']}) [" . implode(', ', $namaKomponen) . "]";
        }
        $ringkasan = "Jadwal:{$idJadwal}|Kelas:{$jadwal['nama_kelas']}|Mapel:{$jadwal['nama_mapel']}"
            . "|KKM:{$kkm}|Kategori:" . implode(';;', $daftarKategoriStr);

        $this->auditLogModel->catat(
            session()->get('id_user'),
            $guru['nama'],
            'Atur Nilai',
            $ringkasan
        );

        return redirect()->to('/guru/nilai/form/' . $idJadwal)
            ->with('success', 'Pengaturan kategori, komponen & bobot nilai tersimpan.');
    }

    public function simpan($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

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

        $this->kirimNotifNilaiJikaLengkap($guru, $jadwal, array_keys($nilai));

        return redirect()->to('/guru/nilai/form/' . $idJadwal)->with('success', 'Nilai berhasil disimpan.');
    }

    protected function kirimNotifNilaiJikaLengkap($guru, array $jadwal, array $idSiswaList): void
    {
        $semesterAktif = $this->semesterModel->getActive();
        $pengaturan = $this->pengaturanModel->getPengaturan(
            (int) $guru['id_guru'],
            (int) $jadwal['id_kelas'],
            (int) $jadwal['id_mapel'],
            (int) $semesterAktif['id_semester']
        );

        if (!$pengaturan) {
            return;
        }

        $komponenList = $this->komponenModel->where('id_pengaturan', $pengaturan['id_pengaturan'])->findAll();
        $kategoriList = $this->kategoriModel->getByPengaturan($pengaturan['id_pengaturan']);
        $idKomponenList = array_column($komponenList, 'id_komponen');

        if (empty($idKomponenList)) {
            return;
        }

        $siswaModel = new SiswaModel();
        $wa = new WhatsappService();

        foreach ($idSiswaList as $idSiswa) {
            $nilaiRows = $this->nilaiModel->where('id_siswa', $idSiswa)
                ->whereIn('id_komponen', $idKomponenList)->findAll();

            $terisi = array_column($nilaiRows, 'nilai', 'id_komponen');

            $lengkap = count($terisi) === count($idKomponenList);
            if (!$lengkap) {
                continue;
            }

            $nilaiAkhir = $this->hitungNilaiAkhirSiswa($komponenList, $kategoriList, $terisi);
            $status = $nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas';

            $siswa = $siswaModel->find($idSiswa);
            if (!$siswa) {
                continue;
            }

            $pesan = "Yth. Orang Tua/Wali dari {$siswa['nama']},\n"
                . "Nilai akhir ananda untuk mata pelajaran {$jadwal['nama_mapel']} sudah keluar.\n"
                . "Nilai Akhir: {$nilaiAkhir} ({$status})\n\n"
                . "Terima kasih.";

            $refKey = "nilai-{$pengaturan['id_pengaturan']}-{$idSiswa}";
            $wa->kirimKeOrtu($siswa, 'nilai', $pesan, $refKey);
        }
    }

    protected function hitungNilaiAkhirSiswa(array $komponenList, array $kategoriList, array $nilaiPerKomponen): float
    {
        $komponenPerKategori = [];
        foreach ($komponenList as $komp) {
            $komponenPerKategori[$komp['id_kategori']][] = $komp;
        }

        $totalNilaiTerbobot = 0;
        $totalBobot = 0;

        foreach ($kategoriList as $kat) {
            $komponenDalamKategori = $komponenPerKategori[$kat['id_kategori']] ?? [];

            $nilaiKategori = 0;
            foreach ($komponenDalamKategori as $komp) {
                $nilaiKomponen = $nilaiPerKomponen[$komp['id_komponen']] ?? 0;
                $nilaiKategori += $nilaiKomponen * ((float) $komp['bobot'] / 100);
            }

            $bobotKategori = (float) $kat['bobot'];
            $totalNilaiTerbobot += $nilaiKategori * $bobotKategori;
            $totalBobot += $bobotKategori;
        }

        return $totalBobot > 0 ? round($totalNilaiTerbobot / $totalBobot, 2) : 0;
    }

    public function riwayat($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

        $jadwal = $this->getJadwalMilikSaya($idJadwal, $guru);

        if (!$jadwal) {
            return redirect()->to('/guru/dashboard')->with('errors', ['403' => 'Jadwal ini bukan milik Anda.']);
        }

        $riwayatList = $this->auditLogModel
            ->where('user_id', session()->get('id_user'))
            ->where('aksi', 'Atur Nilai')
            ->like('keterangan', "Jadwal:{$idJadwal}|", 'after')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('guru/nilai/riwayat', [
            'jadwal'      => $jadwal,
            'riwayatList' => $riwayatList,
        ]);
    }

    public function rekap($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

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

    public function exportPdf($idJadwal)
    {
        try {
            $guru = $this->getGuruLogin();
        } catch (\RuntimeException $e) {
            return $this->redirectGuruTidakDitemukan();
        }

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
                ->with('errors', ['belum' => 'Atur komponen & bobot dulu sebelum export rekap.']);
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

        $html = view('guru/nilai/pdf', [
            'jadwal'     => $jadwal,
            'pengaturan' => $pengaturan,
            'rekap'      => $rekap,
        ]);

        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
        $mpdf->SetTitle('Rekap Nilai');
        $mpdf->WriteHTML($html);
        $mpdf->Output('rekap-nilai_' . $jadwal['nama_kelas'] . '_' . $jadwal['nama_mapel'] . '.pdf', 'D');
    }

    protected function hitungRekap(array $pengaturan, array $kategoriList, array $komponenList, array $siswaList): array
    {
        $komponenPerKategori = [];
        foreach ($komponenList as $komp) {
            $komponenPerKategori[$komp['id_kategori']][] = $komp;
        }

        $idKomponenList = array_column($komponenList, 'id_komponen');
        $rows = $idKomponenList ? $this->nilaiModel->whereIn('id_komponen', $idKomponenList)->findAll() : [];

        $nilaiMap = [];
        foreach ($rows as $row) {
            $nilaiMap[$row['id_siswa']][$row['id_komponen']] = $row['nilai'];
        }

        $hasil = [];
        foreach ($siswaList as $s) {
            $idSiswa = $s['id_siswa'];

            $totalNilaiTerbobot = 0;
            $totalBobot = 0;

            foreach ($kategoriList as $kat) {
                $komponenDalamKategori = $komponenPerKategori[$kat['id_kategori']] ?? [];

                $nilaiKategori = 0;
                foreach ($komponenDalamKategori as $komp) {
                    $nilaiKomponen = $nilaiMap[$idSiswa][$komp['id_komponen']] ?? 0;
                    $nilaiKategori += $nilaiKomponen * ((float) $komp['bobot'] / 100);
                }

                $bobotKategori = (float) $kat['bobot'];
                $totalNilaiTerbobot += $nilaiKategori * $bobotKategori;
                $totalBobot += $bobotKategori;
            }

            $nilaiAkhir = $totalBobot > 0 ? ($totalNilaiTerbobot / $totalBobot) : 0;

            $hasil[] = [
                'nama_siswa'  => $s['nama'],
                'nilai_akhir' => round($nilaiAkhir, 2),
                'status'      => $nilaiAkhir >= $pengaturan['kkm'] ? 'Tuntas' : 'Belum Tuntas',
            ];
        }

        return $hasil;
    }
}