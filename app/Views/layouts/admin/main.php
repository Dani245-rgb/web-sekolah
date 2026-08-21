<!DOCTYPE html>
<html lang="id">

<?php
$pengaturanModelLayout = new \App\Models\PengaturanModel();
$pengaturanLayout      = $pengaturanModelLayout->getPengaturan();
$namaSekolahLayout     = $pengaturanLayout['nama_sekolah'] ?? 'Sistem Sekolah';
?>

<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'Admin' ?> - <?= esc($namaSekolahLayout) ?></title>
    <?php if (!empty($pengaturanLayout['favicon'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('assets/uploads/sekolah/' . $pengaturanLayout['favicon']) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <?= $this->renderSection('styles') ?>
</head>

<body>

    <div class="admin-wrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <?php if (!empty($pengaturanLayout['logo'])): ?>
                    <img src="<?= base_url('assets/uploads/sekolah/' . $pengaturanLayout['logo']) ?>" alt="Logo" style="height:28px;vertical-align:middle;margin-right:8px;">
                <?php endif; ?>
                <span><?= esc(strtoupper($namaSekolahLayout)) ?></span>
            </div>

            <div class="sidebar-profile">
                <div class="profile-avatar">A</div>
                <div>
                    <strong>Admin</strong>
                    <small><?= esc(session()->get('username')) ?></small>
                </div>
            </div>

            <nav class="sidebar-menu">
                <a href="<?= base_url('admin/dashboard') ?>"
                    class="<?= uri_string() === 'admin/dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>

                <p class="menu-label">DATA SEKOLAH</p>
                <a href="<?= base_url('admin/siswa') ?>"
                    class="<?= strpos(uri_string(), 'admin/siswa') === 0 ? 'active' : '' ?>"><i class="bi bi-people"></i> Siswa</a>
                <a href="<?= base_url('admin/guru') ?>"
                    class="<?= strpos(uri_string(), 'admin/guru') === 0 ? 'active' : '' ?>"><i class="bi bi-person-badge"></i> Guru</a>


                <p class="menu-label">DATA AKADEMIK</p>
                <a href="<?= base_url('admin/tahun-ajaran') ?>"
                    class="<?= strpos(uri_string(), 'admin/tahun-ajaran') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar3"></i> Tahun Ajaran</a>
                <a href="<?= base_url('admin/kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/kelas') === 0 && strpos(uri_string(), 'assign-kelas') === false ? 'active' : '' ?>"><i class="bi bi-door-open"></i> Kelas</a>
                <a href="<?= base_url('admin/assign-kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/assign-kelas') === 0 ? 'active' : '' ?>"><i class="bi bi-diagram-3"></i> Assign Kelas</a>
                <a href="<?= base_url('admin/mapel') ?>"
                    class="<?= strpos(uri_string(), 'admin/mapel') === 0 ? 'active' : '' ?>"><i class="bi bi-journal-bookmark"></i> Mata Pelajaran</a>
                <a href="<?= base_url('admin/jadwal') ?>"
                    class="<?= strpos(uri_string(), 'admin/jadwal') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar-week"></i> Jadwal Manager</a>
                <a href="<?= base_url('admin/alumni') ?>"
                    class="<?= strpos(uri_string(), 'admin/alumni') === 0 ? 'active' : '' ?>"><i class="bi bi-mortarboard"></i> Alumni</a>
                <a href="<?= base_url('admin/mutasi') ?>"
                    class="<?= strpos(uri_string(), 'admin/mutasi') === 0 ? 'active' : '' ?>"><i class="bi bi-arrow-left-right"></i> Mutasi</a>
                <a href="<?= base_url('admin/kenaikan-kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/kenaikan-kelas') === 0 ? 'active' : '' ?>"><i class="bi bi-arrow-up-circle"></i> Kenaikan Kelas</a>
                <a href="<?= base_url('admin/riwayat-kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/riwayat-kelas') === 0 ? 'active' : '' ?>"><i class="bi bi-clock-history"></i> Riwayat Kelas</a>

                <p class="menu-label">WEBSITE</p>
                <a href="<?= base_url('admin/bk-artikel') ?>"
                    class="<?= strpos(uri_string(), 'admin/bk-artikel') === 0 ? 'active' : '' ?>"><i class="bi bi-heart-pulse"></i> Artikel BK</a>
                <a href="<?= base_url('admin/unduhan') ?>"
                    class="<?= strpos(uri_string(), 'admin/unduhan') === 0 ? 'active' : '' ?>"><i class="bi bi-download"></i> Pusat Unduhan</a>
                <a href="<?= base_url('admin/jurusan') ?>"
                    class="<?= strpos(uri_string(), 'admin/jurusan') === 0 ? 'active' : '' ?>"><i class="bi bi-diagram-3-fill"></i> Jurusan</a>
                <a href="<?= base_url('admin/berita') ?>"
                    class="<?= strpos(uri_string(), 'admin/berita') === 0 ? 'active' : '' ?>"><i class="bi bi-newspaper"></i> Berita</a>
                <a href="<?= base_url('admin/galeri') ?>"
                    class="<?= strpos(uri_string(), 'admin/galeri') === 0 ? 'active' : '' ?>"><i class="bi bi-images"></i> Galeri</a>
                <a href="<?= base_url('admin/pengumuman') ?>"
                    class="<?= strpos(uri_string(), 'admin/pengumuman') === 0 ? 'active' : '' ?>"><i class="bi bi-megaphone"></i> Pengumuman</a>
                <a href="<?= base_url('admin/prestasi') ?>"
                    class="<?= strpos(uri_string(), 'admin/prestasi') === 0 ? 'active' : '' ?>"><i class="bi bi-trophy"></i> Prestasi</a>
                <a href="<?= base_url('admin/agenda') ?>"
                    class="<?= strpos(uri_string(), 'admin/agenda') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar-event"></i> Agenda</a>
                <a href="<?= base_url('admin/ekstrakurikuler') ?>"
                    class="<?= strpos(uri_string(), 'admin/ekstrakurikuler') === 0 ? 'active' : '' ?>"><i class="bi bi-stars"></i> Ekstrakurikuler</a>
                <a href="<?= base_url('admin/partner') ?>"
                    class="<?= strpos(uri_string(), 'admin/partner') === 0 ? 'active' : '' ?>"><i class="bi bi-briefcase"></i> Industri Mitra</a>
                <a href="<?= base_url('admin/ppdb') ?>"
                    class="<?= strpos(uri_string(), 'admin/ppdb') === 0 ? 'active' : '' ?>"><i class="bi bi-file-earmark-person"></i> PPDB</a>
                <a href="<?= base_url('admin/kontak') ?>"
                    class="<?= strpos(uri_string(), 'admin/kontak') === 0 ? 'active' : '' ?>"><i class="bi bi-envelope"></i> Pesan Masuk</a>
                <a href="<?= base_url('admin/kalender-akademik') ?>"
                    class="<?= strpos(uri_string(), 'admin/kalender-akademik') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar-check"></i> Kalender Akademik</a>
                <a href="<?= base_url('admin/profil-sekolah/sejarah') ?>"
                    class="<?= strpos(uri_string(), 'admin/profil-sekolah') === 0 ? 'active' : '' ?>"><i class="bi bi-building"></i> Profil Sekolah</a>
                <a href="<?= base_url('admin/organisasi') ?>"
                    class="<?= strpos(uri_string(), 'admin/organisasi') === 0 ? 'active' : '' ?>"><i class="bi bi-diagram-2"></i> Organisasi Sekolah</a>


                <p class="menu-label">USER MANAGEMENT</p>
                <a href="<?= base_url('admin/user/admin') ?>"
                    class="<?= uri_string() === 'admin/user' || uri_string() === 'admin/user/admin' ? 'active' : '' ?>"><i class="bi bi-person-gear"></i> Admin</a>
                <a href="<?= base_url('admin/user/guru') ?>"
                    class="<?= uri_string() === 'admin/user/guru' ? 'active' : '' ?>"><i class="bi bi-person-video3"></i> Guru</a>
                <a href="<?= base_url('admin/user/siswa') ?>"
                    class="<?= uri_string() === 'admin/user/siswa' ? 'active' : '' ?>"><i class="bi bi-person"></i> Siswa</a>
                <a href="<?= base_url('admin/role-permission') ?>"
                    class="<?= strpos(uri_string(), 'admin/role-permission') === 0 ? 'active' : '' ?>"><i class="bi bi-shield-lock"></i> Role & Permission</a>

                <p class="menu-label">LAPORAN</p>
                <a href="<?= base_url('admin/laporan-siswa') ?>"
                    class="<?= strpos(uri_string(), 'admin/laporan-siswa') === 0 ? 'active' : '' ?>"><i class="bi bi-file-earmark-text"></i> Laporan Siswa</a>
                <a href="<?= base_url('admin/laporan-akademik') ?>"
                    class="<?= strpos(uri_string(), 'admin/laporan-akademik') === 0 ? 'active' : '' ?>"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Akademik</a>
                <a href="<?= base_url('admin/nilai/rekap') ?>"
                    class="<?= strpos(uri_string(), 'admin/nilai/rekap') === 0 ? 'active' : '' ?>"><i class="bi bi-clipboard-data"></i> Laporan Nilai</a>
                <a href="<?= base_url('admin/rekap-absensi') ?>"
                    class="<?= strpos(uri_string(), 'admin/rekap-absensi') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar2-check"></i> Laporan Absensi</a>

                <p class="menu-label">SISTEM</p>
                <a href="<?= base_url('admin/notifikasi') ?>"
                    class="<?= strpos(uri_string(), 'admin/notifikasi') === 0 ? 'active' : '' ?>"><i class="bi bi-bell"></i> Notifikasi</a>
                <a href="<?= base_url('admin/backup-database') ?>"
                    class="<?= strpos(uri_string(), 'admin/backup-database') === 0 ? 'active' : '' ?>"><i class="bi bi-hdd-stack"></i> Backup Database</a>
                <a href="<?= base_url('admin/pengaturan') ?>"
                    class="<?= strpos(uri_string(), 'admin/pengaturan') === 0 ? 'active' : '' ?>"><i class="bi bi-gear"></i> Pengaturan</a>
                <a href="<?= base_url('admin/audit-log') ?>"
                    class="<?= strpos(uri_string(), 'admin/audit-log') === 0 ? 'active' : '' ?>"><i class="bi bi-journal-text"></i> Audit Log</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <header class="topbar" style="flex-wrap:nowrap;">
                <div style="display:flex;align-items:center;gap:10px;background:#f4f6f9;padding:8px 16px;border-radius:8px;font-size:13px;color:#4b5563;flex-shrink:0;white-space:nowrap;">
                    <i class="bi bi-calendar3"></i>
                    <?php
                    $hariIndo  = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                    $bulanIndo = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];
                    $hariIni   = $hariIndo[date('l')];
                    $bulanIni  = $bulanIndo[date('F')];
                    ?>
                    <span><?= $hariIni ?>, <?= date('d') ?> <?= $bulanIni ?> <?= date('Y') ?></span>
                </div>
                <div class="topbar-right" style="display:flex;align-items:center;gap:16px;flex-shrink:0;white-space:nowrap;">
                    <div style="position:relative;">
                        <a href="<?= base_url('admin/notifikasi') ?>" id="bell-notif" style="position:relative;text-decoration:none;color:#4b5563;font-size:20px;">
                            <i class="bi bi-bell"></i>
                            <span id="badge-notif" style="display:none;position:absolute;top:-6px;right:-8px;background:red;color:white;border-radius:50%;font-size:11px;padding:1px 6px;"></span>
                        </a>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:32px;height:32px;border-radius:50%;background:#3b5f8a;color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0;">
                            <?= esc(strtoupper(substr(session()->get('username'), 0, 1))) ?>
                        </div>
                        <span><?= esc(session()->get('username')) ?> (<?= esc(session()->get('role')) ?>)</span>
                    </div>
                    <a href="<?= base_url('logout') ?>" class="btn-logout">Logout</a>
                </div>
            </header>

            <script>
                (function() {
                    function pollNotif() {
                        fetch('<?= base_url('admin/notifikasi/poll') ?>')
                            .then(res => res.json())
                            .then(data => {
                                const badge = document.getElementById('badge-notif');
                                if (data.unread_count > 0) {
                                    badge.style.display = 'inline-block';
                                    badge.textContent = data.unread_count;
                                } else {
                                    badge.style.display = 'none';
                                }
                            })
                            .catch(() => {});
                    }

                    pollNotif();
                    setInterval(pollNotif, 20000); // cek tiap 20 detik
                })();
            </script>

            <div class="content-body">
                <?= $this->renderSection('content') ?>
            </div>
        </main>

    </div>

</body>

</html>