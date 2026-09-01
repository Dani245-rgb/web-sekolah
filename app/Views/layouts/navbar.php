<nav class="navbar">
    <div class="container">
        <div class="navbar-inner">

            <button class="navbar-toggle" id="navbar-toggle" aria-label="Buka menu" aria-expanded="false" aria-controls="nav-menu">
                <i class="bi bi-list"></i>
            </button>

            <ul class="nav-menu" id="nav-menu">
                <li><a href="<?= base_url('/') ?>">Beranda</a></li>

                <li class="dropdown">
                    <a href="#">Profil <i class="bi bi-chevron-down dropdown-caret"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url('profil/sejarah') ?>"> Sejarah Sekolah </a></li>
                        <li><a href="<?= base_url('profil/visi-misi') ?>"> Visi Misi </a></li>
                        <li><a href="<?= base_url('profil/struktur') ?>"> Struktur Organisasi </a></li>
                        <li><a href="<?= base_url('profil/kepala-sekolah') ?>"> Kepala Sekolah </a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#">Akademik <i class="bi bi-chevron-down dropdown-caret"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url('akademik/jurusan') ?>"> Jurusan </a></li>
                        <li><a href="<?= base_url('akademik/guru') ?>"> Guru & Staff </a></li>
                        <li><a href="<?= base_url('akademik/jadwal') ?>"> Jadwal </a></li>
                        <li><a href="<?= base_url('akademik/kalender') ?>"> Kalender Akademik </a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#">Berita <i class="bi bi-chevron-down dropdown-caret"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url('berita') ?>"> Semua Berita </a></li>
                        <li><a href="<?= base_url('pengumuman') ?>"> Pengumuman </a></li>
                        <li><a href="<?= base_url('prestasi') ?>"> Prestasi </a></li>
                        <li><a href="<?= base_url('agenda') ?>"> Agenda </a></li>
                    </ul>
                </li>

                <li class="dropdown">
                    <a href="#">Layanan BK <i class="bi bi-chevron-down dropdown-caret"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?= base_url('bk/kesehatan-mental') ?>"> Kesehatan Mental </a></li>
                        <li><a href="<?= base_url('bk/karier') ?>"> Karier & Studi Lanjut </a></li>
                        <li><a href="<?= base_url('bk/tes-minat') ?>"> Tes Minat & Bakat </a></li>
                    </ul>
                </li>

                <li><a href="<?= base_url('galeri') ?>"> Galeri </a></li>
                <li><a href="<?= base_url('ekstrakurikuler') ?>"> Ekstrakurikuler </a></li>
                <li><a href="<?= base_url('partner') ?>"> Industri Mitra </a></li>
                <li><a href="<?= base_url('ppdb') ?>"> PPDB </a></li>
                <li><a href="<?= base_url('kontak') ?>"> Kontak </a></li>
                <li><a href="<?= base_url('unduhan') ?>">Download</a></li>
            </ul>

            <div class="nav-actions">
                <form class="search-box" action="<?= base_url('search') ?>" method="get">
                    <button type="submit"><i class="bi bi-search"></i></button>
                    <input type="text" name="q" placeholder="Cari..." value="<?= esc($_GET['q'] ?? '') ?>">
                </form>
                <a class="btn-login" href="<?= base_url('login') ?>">Login</a>
            </div>

        </div>
    </div>
</nav>