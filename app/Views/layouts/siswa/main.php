<?php
$nama = session()->get('username');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Website Sekolah</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/siswa.css') ?>">
</head>

<body>

    <div class="topbar">
        <div class="brand">
            <img src="<?= base_url('assets/logo-sekolah.png') ?>" alt="Logo Sekolah"
                onerror="this.style.display='none'">
            Dashboard Siswa
        </div>
        <div class="user">
            <span>Halo, <?= esc($nama) ?> 👋</span>
            <div class="avatar"><?= strtoupper(substr($nama, 0, 1)) ?></div>
        </div>
    </div>

    <div class="layout">
        <div class="sidebar">
            <a href="<?= base_url('siswa/dashboard') ?>" class="active">🏠 Dashboard</a>
            <a href="#">👤 Profil Saya</a>
            <a href="#">📚 Jadwal Pelajaran</a>
            <a href="#">📝 Nilai</a>
            <a href="#">📅 Absensi</a>
            <a href="#">📢 Pengumuman</a>
            <a href="#">📰 Berita Sekolah</a>
            <a href="#">🎓 PKL</a>
            <a href="#">📂 Tugas</a>
            <a href="#">📥 Download Materi</a>
            <hr>
            <a href="<?= base_url('profil/gantipassword') ?>">⚙️ Pengaturan</a>
            <a href="<?= base_url('logout') ?>">🚪 Logout</a>
        </div>

        <div class="content">
            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-error"><?= esc(session()->getFlashdata('warning')) ?></div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <script src="<?= base_url('assets/js/siswa.js') ?>"></script>
</body>

</html>