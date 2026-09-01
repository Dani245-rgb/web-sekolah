<?php
$namaGuru = $guru['nama'] ?? session()->get('username');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Website Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/guru.css') ?>">
</head>

<body>

    <div class="topbar">
        <button class="btn-toggle-sidebar" onclick="document.querySelector('.sidebar').classList.toggle('collapsed')">
            <i class="bi bi-list"></i>
        </button>
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
            <a href="<?= base_url('guru/dashboard') ?>" class="active"><i class="bi bi-house-door-fill"></i> <span class="label">Dashboard</span></a>
            <a href="<?= base_url('guru/profil') ?>" class="<?= strpos(uri_string(), 'guru/profil') === 0 ? 'active' : '' ?>"><i class="bi bi-person-circle"></i> <span class="label">Profil</span></a>
            <a href="<?= base_url('guru/pkl') ?>" class="<?= strpos(uri_string(), 'guru/pkl') === 0 ? 'active' : '' ?>"><i class="bi bi-briefcase-fill"></i> <span class="label">PKL</span></a>
            <a href="<?= base_url('guru/kelas') ?>" class="<?= strpos(uri_string(), 'guru/kelas') === 0 ? 'active' : '' ?>"><i class="bi bi-building"></i> <span class="label">Data Kelas</span></a>
            <a href="<?= base_url('guru/jadwal') ?>" class="<?= strpos(uri_string(), 'guru/jadwal') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar3"></i> <span class="label">Jadwal</span></a>
            <a href="<?= base_url('guru/tugas') ?>" class="<?= strpos(uri_string(), 'guru/tugas') === 0 ? 'active' : '' ?>"><i class="bi bi-folder-fill"></i> <span class="label">Tugas</span></a>
            <a href="<?= base_url('guru/materi') ?>" class="<?= strpos(uri_string(), 'guru/materi') === 0 ? 'active' : '' ?>"><i class="bi bi-file-earmark-arrow-up-fill"></i> <span class="label">Materi</span></a>
            <a href="<?= base_url('guru/dashboard') ?>#jadwal-hari-ini"><i class="bi bi-pencil-square"></i> <span class="label">Input Nilai</span></a>
            <a href="<?= base_url('guru/dashboard') ?>#jadwal-hari-ini"><i class="bi bi-clipboard-check"></i> <span class="label">Input Absensi</span></a>
            <a href="<?= base_url('guru/pengumuman') ?>" class="<?= strpos(uri_string(), 'guru/pengumuman') === 0 ? 'active' : '' ?>"><i class="bi bi-megaphone-fill"></i> <span class="label">Pengumuman</span></a>
            <a href="#" class="disabled" aria-disabled="true"><i class="bi bi-newspaper"></i> <span class="label">Berita</span><span class="badge-soon">Soon</span></a>
            <a href="<?= base_url('guru/agenda') ?>" class="<?= strpos(uri_string(), 'guru/agenda') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar-event"></i> <span class="label">Agenda</span></a>
            <hr>
            <a href="<?= base_url('profil/gantipassword') ?>"><i class="bi bi-key-fill"></i> <span class="label">Ganti Password</span></a>
            <a href="<?= base_url('logout') ?>" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i> <span class="label">Logout</span></a>
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