<?php
$namaGuru = $guru['nama'] ?? session()->get('username');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Website Sekolah</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/guru.css') ?>">
</head>

<body>

    <div class="topbar">
        <div class="brand">
            <img src="<?= base_url('assets/logo-sekolah.png') ?>" alt="Logo Sekolah"
                onerror="this.style.display='none'">
            Dashboard Guru
        </div>
        <div class="user">
            <span>🔔</span>
            <span><?= esc($namaGuru) ?></span>
            <div class="avatar"><?= strtoupper(substr($namaGuru, 0, 1)) ?></div>
        </div>
    </div>

    <div class="layout">
        <div class="sidebar">
            <a href="<?= base_url('guru/dashboard') ?>" class="active">🏠 Dashboard</a>
            <a href="#">👤 Profil</a>
            <a href="#">🏫 Data Kelas</a>
            <a href="#">📅 Jadwal</a>
            <a href="<?= base_url('guru/dashboard') ?>#jadwal-hari-ini">📝 Input Nilai</a>
            <a href="<?= base_url('guru/dashboard') ?>#jadwal-hari-ini">📋 Input Absensi</a>
            <a href="#">📢 Pengumuman</a>
            <a href="#">📰 Berita</a>
            <a href="#">🗓️ Agenda</a>
            <hr>
            <a href="<?= base_url('profil/gantipassword') ?>">🔑 Ganti Password</a>
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

    <script src="<?= base_url('assets/js/guru.js') ?>"></script>
</body>

</html>