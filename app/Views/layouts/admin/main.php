<!DOCTYPE html>
<html lang="id">

<?php
$pengaturanModelLayout = new \App\Models\PengaturanModel();
$pengaturanLayout      = $pengaturanModelLayout->getPengaturan();
$namaSekolahLayout     = $pengaturanLayout['nama_sekolah'] ?? 'Sistem Sekolah';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin' ?> - <?= esc($namaSekolahLayout) ?></title>
    <?php if (!empty($pengaturanLayout['favicon'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('assets/uploads/sekolah/' . $pengaturanLayout['favicon']) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/dashboard/mobile.css') ?>">
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
                    <i class="bi bi-house-door"></i> <span class="label">Dashboard</span>
                </a>

                <p class="menu-label">DATA SEKOLAH</p>
                <a href="<?= base_url('admin/siswa') ?>"
                    class="<?= strpos(uri_string(), 'admin/siswa') === 0 ? 'active' : '' ?>"><i class="bi bi-people"></i> <span class="label">Siswa</span></a>
                <a href="<?= base_url('admin/guru') ?>"
                    class="<?= strpos(uri_string(), 'admin/guru') === 0 ? 'active' : '' ?>"><i class="bi bi-person-badge"></i> <span class="label">Guru</span></a>


                <p class="menu-label">DATA AKADEMIK</p>
                <a href="<?= base_url('admin/tahun-ajaran') ?>"
                    class="<?= strpos(uri_string(), 'admin/tahun-ajaran') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar3"></i> <span class="label">Tahun Ajaran</span></a>
                <a href="<?= base_url('admin/kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/kelas') === 0 && strpos(uri_string(), 'assign-kelas') === false ? 'active' : '' ?>"><i class="bi bi-door-open"></i> <span class="label">Kelas</span></a>
                <a href="<?= base_url('admin/assign-kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/assign-kelas') === 0 ? 'active' : '' ?>"><i class="bi bi-diagram-3"></i> <span class="label">Assign Kelas</span></a>
                <a href="<?= base_url('admin/mapel') ?>"
                    class="<?= strpos(uri_string(), 'admin/mapel') === 0 ? 'active' : '' ?>"><i class="bi bi-journal-bookmark"></i> <span class="label">Mata Pelajaran</span></a>
                <a href="<?= base_url('admin/jadwal') ?>"
                    class="<?= strpos(uri_string(), 'admin/jadwal') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar-week"></i> <span class="label">Jadwal Manager</span></a>
                <a href="<?= base_url('admin/alumni') ?>"
                    class="<?= strpos(uri_string(), 'admin/alumni') === 0 ? 'active' : '' ?>"><i class="bi bi-mortarboard"></i> <span class="label">Alumni</span></a>
                <a href="<?= base_url('admin/mutasi') ?>"
                    class="<?= strpos(uri_string(), 'admin/mutasi') === 0 ? 'active' : '' ?>"><i class="bi bi-arrow-left-right"></i> <span class="label">Mutasi</span></a>
                <a href="<?= base_url('admin/kenaikan-kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/kenaikan-kelas') === 0 ? 'active' : '' ?>"><i class="bi bi-arrow-up-circle"></i> <span class="label">Kenaikan Kelas</span></a>
                <a href="<?= base_url('admin/riwayat-kelas') ?>"
                    class="<?= strpos(uri_string(), 'admin/riwayat-kelas') === 0 ? 'active' : '' ?>"><i class="bi bi-clock-history"></i> <span class="label">Riwayat Kelas</span></a>

                <p class="menu-label">WEBSITE</p>
                <a href="<?= base_url('admin/bk-artikel') ?>"
                    class="<?= strpos(uri_string(), 'admin/bk-artikel') === 0 ? 'active' : '' ?>"><i class="bi bi-heart-pulse"></i> <span class="label">Artikel BK</span></a>
                <a href="<?= base_url('admin/unduhan') ?>"
                    class="<?= strpos(uri_string(), 'admin/unduhan') === 0 ? 'active' : '' ?>"><i class="bi bi-download"></i> <span class="label">Pusat Unduhan</span></a>
                <a href="<?= base_url('admin/jurusan') ?>"
                    class="<?= strpos(uri_string(), 'admin/jurusan') === 0 ? 'active' : '' ?>"><i class="bi bi-diagram-3-fill"></i> <span class="label">Jurusan</span></a>
                <a href="<?= base_url('admin/berita') ?>"
                    class="<?= strpos(uri_string(), 'admin/berita') === 0 ? 'active' : '' ?>"><i class="bi bi-newspaper"></i> <span class="label">Berita</span></a>
                <a href="<?= base_url('admin/galeri') ?>"
                    class="<?= strpos(uri_string(), 'admin/galeri') === 0 ? 'active' : '' ?>"><i class="bi bi-images"></i> <span class="label">Galeri</span></a>
                <a href="<?= base_url('admin/pengumuman') ?>"
                    class="<?= strpos(uri_string(), 'admin/pengumuman') === 0 ? 'active' : '' ?>"><i class="bi bi-megaphone"></i> <span class="label">Pengumuman</span></a>
                <a href="<?= base_url('admin/prestasi') ?>"
                    class="<?= strpos(uri_string(), 'admin/prestasi') === 0 ? 'active' : '' ?>"><i class="bi bi-trophy"></i> <span class="label">Prestasi</span></a>
                <a href="<?= base_url('admin/agenda') ?>"
                    class="<?= strpos(uri_string(), 'admin/agenda') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar-event"></i> <span class="label">Agenda</span></a>
                <a href="<?= base_url('admin/ekstrakurikuler') ?>"
                    class="<?= strpos(uri_string(), 'admin/ekstrakurikuler') === 0 ? 'active' : '' ?>"><i class="bi bi-stars"></i> <span class="label">Ekstrakurikuler</span></a>
                <a href="<?= base_url('admin/partner') ?>"
                    class="<?= strpos(uri_string(), 'admin/partner') === 0 ? 'active' : '' ?>"><i class="bi bi-briefcase"></i> <span class="label">Industri Mitra</span></a>
                <a href="<?= base_url('admin/ppdb') ?>"
                    class="<?= strpos(uri_string(), 'admin/ppdb') === 0 && strpos(uri_string(), 'pengaturan') === false ? 'active' : '' ?>"><i class="bi bi-file-earmark-person"></i> <span class="label">PPDB</span></a>
                <a href="<?= base_url('admin/ppdb/pengaturan') ?>"
                    class="<?= strpos(uri_string(), 'admin/ppdb/pengaturan') === 0 ? 'active' : '' ?>"><i class="bi bi-toggle-on"></i> <span class="label">Buka/Tutup PPDB</span></a>
                <a href="<?= base_url('admin/kontak') ?>"
                    class="<?= strpos(uri_string(), 'admin/kontak') === 0 ? 'active' : '' ?>"><i class="bi bi-envelope"></i> <span class="label">Pesan Masuk</span></a>
                <a href="<?= base_url('admin/kalender-akademik') ?>"
                    class="<?= strpos(uri_string(), 'admin/kalender-akademik') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar-check"></i> <span class="label">Kalender Akademik</span></a>
                <a href="<?= base_url('admin/profil-sekolah/sejarah') ?>"
                    class="<?= strpos(uri_string(), 'admin/profil-sekolah') === 0 ? 'active' : '' ?>"><i class="bi bi-building"></i> <span class="label">Profil Sekolah</span></a>
                <a href="<?= base_url('admin/organisasi') ?>"
                    class="<?= strpos(uri_string(), 'admin/organisasi') === 0 ? 'active' : '' ?>"><i class="bi bi-diagram-2"></i> <span class="label">Organisasi Sekolah</span></a>


                <?php if (session()->get('is_superadmin')): ?>
                <p class="menu-label">USER MANAGEMENT</p>
                <a href="<?= base_url('admin/user/admin') ?>"
                    class="<?= uri_string() === 'admin/user' || uri_string() === 'admin/user/admin' ? 'active' : '' ?>"><i class="bi bi-person-gear"></i> <span class="label">Admin</span></a>
                <a href="<?= base_url('admin/role-permission') ?>"
                    class="<?= strpos(uri_string(), 'admin/role-permission') === 0 ? 'active' : '' ?>"><i class="bi bi-shield-lock"></i> <span class="label">Role & Permission</span></a>
                <?php endif; ?>

                <p class="menu-label">SISTEM</p>
                <a href="<?= base_url('admin/notifikasi') ?>"
                    class="<?= strpos(uri_string(), 'admin/notifikasi') === 0 ? 'active' : '' ?>"><i class="bi bi-bell"></i> <span class="label">Notifikasi</span></a>
                <?php if (session()->get('is_superadmin')): ?>
                <a href="<?= base_url('admin/backup-database') ?>"
                    class="<?= strpos(uri_string(), 'admin/backup-database') === 0 ? 'active' : '' ?>"><i class="bi bi-hdd-stack"></i> <span class="label">Backup Database</span></a>
                <?php endif; ?>
                <a href="<?= base_url('admin/pengaturan') ?>"
                    class="<?= strpos(uri_string(), 'admin/pengaturan') === 0 ? 'active' : '' ?>"><i class="bi bi-gear"></i> <span class="label">Pengaturan</span></a>
                <?php if (session()->get('is_superadmin')): ?>
                <a href="<?= base_url('admin/audit-log') ?>"
                    class="<?= strpos(uri_string(), 'admin/audit-log') === 0 ? 'active' : '' ?>"><i class="bi bi-journal-text"></i> <span class="label">Audit Log</span></a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <header class="topbar" style="flex-wrap:nowrap;">
                <button class="btn-toggle-sidebar" onclick="document.querySelector('.sidebar').classList.toggle('collapsed')">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-date" style="display:flex;align-items:center;gap:10px;background:#f4f6f9;padding:8px 16px;border-radius:8px;font-size:13px;color:#4b5563;flex-shrink:0;white-space:nowrap;">
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
                        <span class="topbar-username"><?= esc(session()->get('username')) ?> (<?= esc(session()->get('role')) ?>)</span>
                    </div>
                    <a href="<?= base_url('logout') ?>" class="btn-logout" onclick="return confirm('Yakin ingin logout?')">Logout</a>
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

    <script src="<?= base_url('assets/js/admin.js') ?>"></script>

</body>

</html>