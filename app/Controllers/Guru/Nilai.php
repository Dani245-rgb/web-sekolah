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

    protected function redirectGuruTidakDitemukan()
    {
        return redirect()->to('/logout')
            ->with('errors', ['akun' => 'Data guru Anda tidak ditemukan. Silakan hubungi Admin.']);
    }

    /**
     * Dipanggil via AJAX secara berkala (keep-alive) dan tepat sebelum submit form nilai.
     * Efeknya: 1) request ke server ini memperpanjang umur session CI4 secara alami,
     * 2) mengembalikan CSRF token TERBARU yang bisa disuntikkan ke form sebelum submit,
     * supaya token yang dipakai submit selalu segar walau guru sudah lama di halaman ini.
     */
    public function csrfToken()
    {
        try {
            $this->getGuruLogin(); // sekalian pastikan session guru masih valid
        } catch (\RuntimeException $e) {
            return $this->response->setStatusCode(401)->setJSON([
                'valid' => false,
                'pesan' => 'Session tidak valid, silakan login ulang.',
            ]);
        }

        return $this->response->setJSON([
            'valid'      => true,
            'csrfName'   => csrf_token(),
            'csrfHash'   => csrf_hash(),
        ]);
    }

    protected function getJadwalMilikSaya($idJadwal, $guru)
    {
        $jadwalModel = new JadwalModel();
        $jadwal = $jadwalModel->select('jadwal.*, kelas.nama_kelas, mapel.nama_mapel, mapel.ada_nilai')
            ->join('kelas', 'kelas.id_kelas = jadwal.id_kelas')
            ->join('mapel', 'mapel.id_mapel = jadwal.id_mapel')
            ->find($idJadwal);

        if (!$jadwal || $jadwal['id_guru'] != $guru['id_guru']) {
            return null;
        }

        return $jadwal;
    }

    protected function redirectJikaMapelTanpaNilai($jadwal)
    {
        if (($jadwal['ada_nilai'] ?? 'Ya') === 'Tidak') {
            return redirect()->to('/guru/dashboard')
                ->with('errors', ['mapel' => "Mapel {$jadwal['nama_mapel']} tidak memerlukan input nilai."]);
        }

        return null;
    }

    /**
     * Sanitasi input nilai: ubah koma jadi titik, trim spasi.
     * Return null kalau bukan angka valid (biar bisa di-skip pemanggil, bukan crash).
     */
    protected function sanitasiNilai($nilaiRaw): ?float
    {
        $nilaiRaw = str_replace(',', '.', trim((string) $nilaiRaw));
        if ($nilaiRaw === '' || !is_numeric($nilaiRaw)) {
            return null;
        }
        $val = (float) $nilaiRaw;

        // Range checking: nilai wajib 0-100
        if ($val < 0 || $val > 100) {
            return null;
        }

        return $val;
    }

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

        if ($blokir = $this->redirectJikaMapelTanpaNilai($jadwal)) {
            return $blokir;
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

        if ($blokir = $this->redirectJikaMapelTanpaNilai($jadwal)) {
            return $blokir;
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
        $nilaiUpdatedAtMap = [];
        foreach ($nilaiRows as $row) {
            $nilaiMap[$row['id_siswa']][$row['id_komponen']] = $row['nilai'];
            // Dipakai form sebagai "snapshot" utk optimistic locking saat simpan()
            $nilaiUpdatedAtMap[$row['id_siswa']][$row['id_komponen']] = $row['updated_at'] ?? null;
        }

        return view('guru/nilai/input', [
            'jadwal'              => $jadwal,
            'pengaturan'          => $pengaturan,
            'komponenList'        => $komponenList,
            'kategoriList'        => $kategoriList,
            'komponenPerKategori' => $komponenPerKategori,
            'siswaList'           => $siswaList,
            'nilaiMap'            => $nilaiMap,
            'nilaiUpdatedAtMap'   => $nilaiUpdatedAtMap,
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

        if ($blokir = $this->redirectJikaMapelTanpaNilai($jadwal)) {
            return $blokir;
        }

        $semesterAktif = $this->semesterModel->getActive();

        $kkm          = (int) $this->request->getPost('kkm');
        $kategoriData = $this->request->getPost('kategori');

        // Range checking: KKM wajib 0-100
        if ($kkm < 0 || $kkm > 100) {
            return redirect()->back()->withInput()->with('errors', ['kkm' => 'KKM harus di antara 0 sampai 100.']);
        }

        if (empty($kategoriData) || !is_array($kategoriData)) {
            return redirect()->back()->withInput()->with('errors', ['kosong' => 'Kategori & komponen nilai wajib diisi.']);
        }

        $totalBobotKategori = 0;

        foreach ($kategoriData as $kat) {
            // FIX #2: bobot kategori wajib > 0, bukan cuma "tidak kosong"
            if (empty($kat['nama']) || $kat['bobot'] === '' || (float) $kat['bobot'] <= 0 || empty($kat['komponen'])) {
                return redirect()->back()->withInput()->with('errors', ['kosong' => 'Setiap kategori wajib punya nama, bobot lebih dari 0, dan minimal 1 komponen.']);
            }

            $totalBobotKategori += (float) $kat['bobot'];

            $totalBobotKomponen = 0;
            foreach ($kat['komponen'] as $komp) {
                if (empty($komp['nama']) || $komp['bobot'] === '') {
                    return redirect()->back()->withInput()->with('errors', ['kosong' => 'Setiap komponen wajib punya nama & bobot.']);
                }

                // Validasi link_referensi: harus URL valid kalau diisi
                if (!empty($komp['link'])) {
                    $linkValid = filter_var($komp['link'], FILTER_VALIDATE_URL)
                        && preg_match('/^https?:\/\//i', $komp['link']); // wajib http/https, tolak javascript:, data:, dll

                    if (!$linkValid) {
                        return redirect()->back()->withInput()->with('errors', [
                            'link' => "Link referensi pada komponen \"{$komp['nama']}\" harus berupa URL http/https yang valid."
                        ]);
                    }
                }

                // Batasi panjang keterangan biar gak dipakai buat payload panjang
                if (!empty($komp['keterangan']) && mb_strlen($komp['keterangan']) > 500) {
                    return redirect()->back()->withInput()->with('errors', [
                        'keterangan' => "Keterangan pada komponen \"{$komp['nama']}\" maksimal 500 karakter."
                    ]);
                }

                $totalBobotKomponen += (float) $komp['bobot'];
            }

            if ($totalBobotKomponen != 100) {
                return redirect()->back()->withInput()->with('errors', [
                    'bobot' => "Total bobot komponen dalam kategori \"{$kat['nama']}\" harus 100%, saat ini {$totalBobotKomponen}%."
                ]);
            }
        }

        // FIX #2: cegah semua kategori dikasih bobot 0 (mencegah division-by-zero di kalkulasi akhir)
        if ($totalBobotKategori <= 0) {
            return redirect()->back()->withInput()->with('errors', ['bobot' => 'Total bobot semua kategori tidak boleh 0.']);
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
            $this->bersihkanKomponenLama($idPengaturan);
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

        if ($blokir = $this->redirectJikaMapelTanpaNilai($jadwal)) {
            return $blokir;
        }

        $nilai = $this->request->getPost('nilai');
        // FIX #4: timestamp snapshot per siswa+komponen, dikirim dari form (hidden input),
        // dipakai utk optimistic locking. Format: updated_at[id_siswa][id_komponen] = 'Y-m-d H:i:s' atau '' kalau baru.
        $updatedAtSnapshot = $this->request->getPost('updated_at') ?? [];

        if (empty($nilai) || !is_array($nilai)) {
            return redirect()->back()->with('errors', ['kosong' => 'Tidak ada nilai yang dikirim.']);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $konflikList = [];
        $dilewatiFormatSalah = [];
        $komponenDihapus = [];

        // Ambil semua id_komponen yg dikirim form, cek sekali di awal (bukan query berulang per sel)
        $semuaIdKomponenDikirim = [];
        foreach ($nilai as $perKomponen) {
            foreach ($perKomponen as $id_komponen => $v) {
                $semuaIdKomponenDikirim[$id_komponen] = true;
            }
        }
        $komponenMasihAda = $semuaIdKomponenDikirim
            ? array_column($this->komponenModel->whereIn('id_komponen', array_keys($semuaIdKomponenDikirim))->findAll(), 'id_komponen')
            : [];
        $komponenMasihAda = array_flip($komponenMasihAda); // biar isset() O(1)

        foreach ($nilai as $id_siswa => $perKomponen) {
            foreach ($perKomponen as $id_komponen => $nilaiValueRaw) {
                if ($nilaiValueRaw === '' || $nilaiValueRaw === null) continue;

                // FIX #5: komponen sudah dihapus (soft-deleted) tepat saat guru mengisi form — jangan simpan, jangan crash FK
                if (!isset($komponenMasihAda[$id_komponen])) {
                    $komponenDihapus[] = $id_komponen;
                    continue;
                }

                // FIX #3: sanitasi koma -> titik, skip kalau tetap bukan angka (jangan crash)
                $nilaiValue = $this->sanitasiNilai($nilaiValueRaw);
                if ($nilaiValue === null) {
                    $dilewatiFormatSalah[] = "{$id_siswa}-{$id_komponen}";
                    continue;
                }
                $existing = $this->nilaiModel->where('id_siswa', $id_siswa)
                    ->where('id_komponen', $id_komponen)->first();

                // FIX #4: optimistic locking — kalau data sudah berubah sejak form dibuka, skip & catat konflik
                $snapshotDikirim = $updatedAtSnapshot[$id_siswa][$id_komponen] ?? '';
                $updatedAtSekarang = $existing['updated_at'] ?? null;

                if ($existing && $snapshotDikirim !== '' && $snapshotDikirim !== (string) $updatedAtSekarang) {
                    $konflikList[] = [
                        'id_siswa'    => $id_siswa,
                        'id_komponen' => $id_komponen,
                        'nilai_baru_ditolak' => $nilaiValue,
                        'nilai_saat_ini'     => $existing['nilai'],
                    ];
                    continue; // jangan timpa nilai yang sudah diubah pihak lain
                }

                if ($existing) {
                    $this->nilaiModel->update($existing['id_nilai'], [
                        'nilai'      => $nilaiValue,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $this->nilaiModel->insert([
                        'id_siswa'    => $id_siswa,
                        'id_komponen' => $id_komponen,
                        'nilai'       => $nilaiValue,
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('errors', ['gagal' => 'Terjadi kesalahan, nilai tidak tersimpan.']);
        }

        // $this->kirimNotifNilaiJikaLengkap($guru, $jadwal, array_keys($nilai));

        if (!empty($komponenDihapus)) {
            return redirect()->to('/guru/nilai/form/' . $idJadwal)
                ->with('errors', ['komponen_dihapus' => 'Beberapa komponen nilai sudah dihapus oleh Admin/Kurikulum saat Anda mengisi form ini, sehingga nilainya tidak tersimpan. Silakan muat ulang halaman ini untuk melihat komponen terbaru.']);
        }

        if (!empty($konflikList)) {
            session()->setFlashdata('konflikNilai', $konflikList);
            return redirect()->to('/guru/nilai/form/' . $idJadwal)
                ->with('errors', ['konflik' => count($konflikList) . ' nilai tidak tersimpan karena sudah diubah oleh pengguna lain. Silakan cek ulang dan isi kembali data tersebut.']);
        }

        return redirect()->to('/guru/nilai/form/' . $idJadwal)->with('success', 'Nilai berhasil disimpan.');
    }

    /**
     * Bersihkan komponen lama sebelum pengaturan ditulis ulang.
     * - Komponen yang BELUM PERNAH punya nilai_siswa: hard-delete beneran (aman, tidak ada yg perlu dilindungi).
     * - Komponen yang SUDAH punya nilai_siswa: soft-delete saja (histori nilai tetap terhubung, tidak numpuk sia-sia
     *   karena hanya terjadi untuk komponen yang memang pernah dipakai, bukan tiap kali guru klik simpan).
     */
    protected function bersihkanKomponenLama(int $idPengaturan): void
    {
        $komponenLama = $this->komponenModel->where('id_pengaturan', $idPengaturan)->findAll();
        if (empty($komponenLama)) {
            return;
        }

        $idKomponenLama = array_column($komponenLama, 'id_komponen');

        $idKomponenPernahDinilai = array_unique(array_column(
            $this->nilaiModel->select('id_komponen')->whereIn('id_komponen', $idKomponenLama)->findAll(),
            'id_komponen'
        ));

        $idUntukHapusPermanen = array_diff($idKomponenLama, $idKomponenPernahDinilai);
        $idUntukSoftDelete    = $idKomponenPernahDinilai;

        if (!empty($idUntukHapusPermanen)) {
            $this->komponenModel->whereIn('id_komponen', $idUntukHapusPermanen)->delete(null, true); // true = forceDelete (hard)
        }

        if (!empty($idUntukSoftDelete)) {
            $this->komponenModel->whereIn('id_komponen', $idUntukSoftDelete)->delete(); // soft delete biasa
        }
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


    protected function sanitasiNamaFile(string $str): string
    {
        // Hanya izinkan huruf, angka, spasi, dash, underscore
        $str = preg_replace('/[^A-Za-z0-9\s\-_]/', '', $str);
        return trim($str);
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

        $namaKelasAman = $this->sanitasiNamaFile($jadwal['nama_kelas']);
        $namaMapelAman = $this->sanitasiNamaFile($jadwal['nama_mapel']);
        $mpdf->Output('rekap-nilai_' . $namaKelasAman . '_' . $namaMapelAman . '.pdf', 'D');
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
