<nav class="navbar">
    <div class="container">

        <ul class="nav-menu">
            <li>
                <a href="<?= base_url('/') ?>">
                    <i class="fa-solid fa-house"></i>
                    Beranda
                </a>
            </li>

            <li class="dropdown">
                <a href="#">
                    Profil
                    <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?= base_url('profil/sejarah') ?>"> Sejarah Sekolah </a></li>
                    <li><a href="<?= base_url('profil/visi-misi') ?>"> Visi Misi </a></li>
                    <li><a href="<?= base_url('profil/struktur') ?>"> Struktur Organisasi </a></li>
                    <li><a href="<?= base_url('profil/kepala-sekolah') ?>"> Kepala Sekolah </a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#">
                    Akademik
                    <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?= base_url('akademik/guru') ?>"> Guru & Staff </a></li>
                    <li><a href="<?= base_url('akademik/jadwal') ?>"> Jadwal </a></li>
                    <li><a href="<?= base_url('akademik/kalender') ?>"> Kalender Akademik </a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#">
                    Berita
                    <i class="fa-solid fa-chevron-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="<?= base_url('berita') ?>"> Semua Berita </a></li>
                    <li><a href="<?= base_url('pengumuman') ?>"> Pengumuman </a></li>
                    <li><a href="<?= base_url('prestasi') ?>"> Prestasi </a></li>
                    <li><a href="<?= base_url('agenda') ?>"> Agenda </a></li>
                </ul>
            </li>

            <li><a href="<?= base_url('galeri') ?>"> Galeri </a></li>
            <li><a href="<?= base_url('ekstrakurikuler') ?>"> Ekstrakurikuler </a></li>
            <li><a href="<?= base_url('partner') ?>"> Industri Mitra </a></li>
            <li><a href="<?= base_url('ppdb') ?>"> PPDB </a></li>
            <li><a href="<?= base_url('kontak') ?>"> Kontak </a></li>
        </ul>

        <div class="nav-actions">
            <div class="search-box">
                <button type="submit">
                    <i class="bi bi-search"></i>
                </button>
                <input type="text" placeholder="Cari...">
            </div>
            <a class="btn-login" href="<?= base_url('login') ?>">Login</a>
        </div>

    </div>
</nav>