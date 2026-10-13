<?php
$seg = service('uri')->getSegment(1);
$aktif = static fn(array $list): string => in_array($seg, $list, true) ? 'active' : '';
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-inner">

            <button class="navbar-toggle" id="navbar-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="nav-menu">
                <i class="bi bi-list"></i>
            </button>

            <ul class="nav-menu" id="nav-menu">
                <li><a href="<?= base_url('/') ?>" class="<?= $aktif(['']) ?>">Beranda</a></li>

                <li class="dropdown">
                    <a href="#" class="<?= $aktif(['profil']) ?>">Profil <i class="bi bi-chevron-down dropdown-caret"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url('profil/sejarah') ?>">Sejarah Sekolah</a></li>
                        <li><a href="<?= base_url('profil/visi-misi') ?>">Visi Misi</a></li>
                        <li><a href="<?= base_url('profil/struktur') ?>">Struktur Organisasi</a></li>
                        <li><a href="<?= base_url('profil/kepala-sekolah') ?>">Kepala Sekolah</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="<?= $aktif(['akademik']) ?>">Akademik <i class="bi bi-chevron-down dropdown-caret"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url('akademik/jurusan') ?>">Jurusan</a></li>
                        <li><a href="<?= base_url('akademik/guru') ?>">Guru &amp; Staff</a></li>
                        <li><a href="<?= base_url('akademik/kalender') ?>">Kalender Akademik</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="<?= $aktif(['berita', 'pengumuman', 'prestasi', 'agenda']) ?>">Berita <i class="bi bi-chevron-down dropdown-caret"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url('berita') ?>">Semua Berita</a></li>
                        <li><a href="<?= base_url('pengumuman') ?>">Pengumuman</a></li>
                        <li><a href="<?= base_url('prestasi') ?>">Prestasi</a></li>
                        <li><a href="<?= base_url('agenda') ?>">Agenda</a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#" class="<?= $aktif(['bk']) ?>">Layanan BK <i class="bi bi-chevron-down dropdown-caret"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url('bk/kesehatan-mental') ?>">Kesehatan Mental</a></li>
                        <li><a href="<?= base_url('bk/karier') ?>">Karier &amp; Studi Lanjut</a></li>
                        <li><a href="<?= base_url('bk/tes-minat') ?>">Tes Minat &amp; Bakat</a></li>
                    </ul>
                </li>

                <li><a href="<?= base_url('galeri') ?>" class="<?= $aktif(['galeri']) ?>">Galeri</a></li>
                <li><a href="<?= base_url('ekstrakurikuler') ?>" class="<?= $aktif(['ekstrakurikuler']) ?>">Ekstrakurikuler</a></li>
                <li><a href="<?= base_url('partner') ?>" class="<?= $aktif(['partner']) ?>">Industri Mitra</a></li>
                <li><a href="<?= base_url('ppdb') ?>" class="<?= $aktif(['ppdb']) ?>">PPDB</a></li>
                <li><a href="<?= base_url('kontak') ?>" class="<?= $aktif(['kontak']) ?>">Kontak</a></li>
                <li><a href="<?= base_url('unduhan') ?>" class="<?= $aktif(['unduhan']) ?>">Download</a></li>
            </ul>

            <div class="nav-actions">
                <form class="search-box" action="<?= base_url('search') ?>" method="get">
                    <button type="submit" aria-label="Cari"><i class="bi bi-search"></i></button>
                    <input type="text" name="q" placeholder="Cari..." value="<?= esc($_GET['q'] ?? '') ?>">
                </form>
                <a class="btn-login btn-gold btn-nav" href="<?= base_url('login') ?>">Login</a>
            </div>

        </div>
    </div>
</nav>