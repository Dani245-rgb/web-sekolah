<?php
$nama = session()->get('username');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Website Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/siswa.css') ?>">
</head>

<body>

    <div class="topbar">
        <button class="btn-toggle-sidebar" onclick="document.querySelector('.sidebar').classList.toggle('collapsed')">
            <i class="bi bi-list"></i>
        </button>
        <div class="brand">
            <img src="<?= base_url('assets/logo-sekolah.png') ?>" alt="Logo Sekolah"
                onerror="this.style.display='none'">
            Dashboard Siswa
        </div>
        <div class="user">
            <span>Halo, <?= esc($nama) ?> <i class="bi bi-hand-thumbs-up-fill" style="color:#f1c40f;"></i></span>
            <div class="avatar"><?= strtoupper(substr($nama, 0, 1)) ?></div>
        </div>
    </div>

    <div class="layout">
        <div class="sidebar">
            <a href="<?= base_url('siswa/dashboard') ?>" class="active"><i class="bi bi-house-door-fill"></i> <span class="label">Dashboard</span></a>
            <a href="<?= base_url('siswa/profil') ?>" class="<?= strpos(uri_string(), 'siswa/profil') === 0 ? 'active' : '' ?>"><i class="bi bi-person-circle"></i> <span class="label">Profil Saya</span></a>
            <a href="<?= base_url('siswa/jadwal') ?>" class="<?= strpos(uri_string(), 'siswa/jadwal') === 0 ? 'active' : '' ?>"><i class="bi bi-calendar3"></i> <span class="label">Jadwal Pelajaran</span></a>
            <a href="<?= base_url('siswa/nilai') ?>" class="<?= strpos(uri_string(), 'siswa/nilai') === 0 ? 'active' : '' ?>"><i class="bi bi-pencil-square"></i> <span class="label">Nilai</span></a>
            <a href="<?= base_url('siswa/absensi') ?>" class="<?= strpos(uri_string(), 'siswa/absensi') === 0 ? 'active' : '' ?>"><i class="bi bi-clipboard-check"></i> <span class="label">Absensi</span></a>
            <a href="<?= base_url('siswa/pengumuman') ?>" class="<?= strpos(uri_string(), 'siswa/pengumuman') === 0 ? 'active' : '' ?>"><i class="bi bi-megaphone-fill"></i> <span class="label">Pengumuman</span></a>
            <a href="#" class="disabled" aria-disabled="true"><i class="bi bi-newspaper"></i> <span class="label">Berita Sekolah</span><span class="badge-soon">Soon</span></a>
            <a href="<?= base_url('siswa/pkl') ?>" class="<?= strpos(uri_string(), 'siswa/pkl') === 0 ? 'active' : '' ?>"><i class="bi bi-mortarboard-fill"></i> <span class="label">PKL</span></a>
            <a href="<?= base_url('siswa/tugas') ?>" class="<?= strpos(uri_string(), 'siswa/tugas') === 0 ? 'active' : '' ?>"><i class="bi bi-folder-fill"></i> <span class="label">Tugas</span></a>
            <a href="<?= base_url('siswa/materi') ?>" class="<?= strpos(uri_string(), 'siswa/materi') === 0 ? 'active' : '' ?>"><i class="bi bi-download"></i> <span class="label">Download Materi</span></a>
            <hr>
            <a href="<?= base_url('profil/gantipassword') ?>"><i class="bi bi-gear-fill"></i> <span class="label">Pengaturan</span></a>
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

    <script src="<?= base_url('assets/js/siswa.js') ?>"></script>
</body>

</html>