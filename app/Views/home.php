<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="content-layout">

            <div class="main-content">

                <!-- ============================= -->
                <!-- Hero -->
                <!-- ============================= -->
                <div class="hero">
                    <img src="<?= base_url('assets/images/hero/hero1.jpg') ?>" alt="Hero SMK Attaufiqiyyah">
                </div>

                <!-- ============================= -->
                <!-- Berita Utama -->
                <!-- ============================= -->
                <div class="section-heading" style="margin-top: 24px;">
                    <h2>Berita Utama</h2>
                    <a href="<?= base_url('berita') ?>" class="lihat-semua">
                        Lihat Semua <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="berita-utama-list">
                    <?php // foreach ($berita_utama as $b): ?>
                    <a href="#" class="berita-card">
                        <img src="<?= base_url('assets/images/berita/contoh.jpg') ?>" alt="">
                        <div class="berita-card-body">
                            <span class="berita-kategori">Akademik</span>
                            <h3>Judul berita contoh di sini</h3>
                            <span class="berita-meta">10 Juli 2026</span>
                        </div>
                    </a>
                    <a href="#" class="berita-card">
                        <img src="<?= base_url('assets/images/berita/contoh2.jpg') ?>" alt="">
                        <div class="berita-card-body">
                            <span class="berita-kategori">Prestasi</span>
                            <h3>Judul berita kedua di sini</h3>
                            <span class="berita-meta">9 Juli 2026</span>
                        </div>
                    </a>
                    <a href="#" class="berita-card">
                        <img src="<?= base_url('assets/images/berita/contoh3.jpg') ?>" alt="">
                        <div class="berita-card-body">
                            <span class="berita-kategori">Kegiatan</span>
                            <h3>Judul berita ketiga di sini</h3>
                            <span class="berita-meta">8 Juli 2026</span>
                        </div>
                    </a>
                    <a href="#" class="berita-card">
                        <img src="<?= base_url('assets/images/berita/contoh4.jpg') ?>" alt="">
                        <div class="berita-card-body">
                            <span class="berita-kategori">Umum</span>
                            <h3>Judul berita keempat di sini</h3>
                            <span class="berita-meta">7 Juli 2026</span>
                        </div>
                    </a>
                    <?php // endforeach; ?>
                </div>
            </div>

            <?= $this->include('layouts/sidebar') ?>

        </div>

        <!-- ============================= -->
        <!-- Berita Terbaru | Agenda Sekolah -->
        <!-- ============================= -->
        <div class="section-split">
            <div>
                <div class="section-heading">
                    <h2>Berita Terbaru</h2>
                    <a href="<?= base_url('berita') ?>" class="lihat-semua">
                        Lihat Semua <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <div class="berita-list">
                    <?php // foreach ($berita_terbaru as $b): ?>
                    <a href="#" class="berita-list-item">
                        <img src="<?= base_url('assets/images/berita/contoh.jpg') ?>" alt="">
                        <div class="berita-list-body">
                            <span class="berita-kategori">Akademik</span>
                            <h3>Judul berita terbaru contoh di sini</h3>
                            <span class="berita-meta">10 Juli 2026</span>
                        </div>
                    </a>
                    <a href="#" class="berita-list-item">
                        <img src="<?= base_url('assets/images/berita/contoh2.jpg') ?>" alt="">
                        <div class="berita-list-body">
                            <span class="berita-kategori">Umum</span>
                            <h3>Judul berita terbaru kedua</h3>
                            <span class="berita-meta">9 Juli 2026</span>
                        </div>
                    </a>
                    <a href="#" class="berita-list-item">
                        <img src="<?= base_url('assets/images/berita/contoh3.jpg') ?>" alt="">
                        <div class="berita-list-body">
                            <span class="berita-kategori">Prestasi</span>
                            <h3>Judul berita terbaru ketiga</h3>
                            <span class="berita-meta">8 Juli 2026</span>
                        </div>
                    </a>
                    <a href="#" class="berita-list-item">
                        <img src="<?= base_url('assets/images/berita/contoh4.jpg') ?>" alt="">
                        <div class="berita-list-body">
                            <span class="berita-kategori">Kegiatan</span>
                            <h3>Judul berita terbaru keempat</h3>
                            <span class="berita-meta">7 Juli 2026</span>
                        </div>
                    </a>
                    <?php // endforeach; ?>
                </div>
            </div>

            <div class="widget-agenda">
                <div class="widget-info-header">
                    <i class="fa-solid fa-calendar-days"></i>
                    <h3>Agenda Sekolah</h3>
                </div>
                <ul class="agenda-list">
                    <?php // foreach ($agenda as $a): ?>
                    <li>
                        <a href="#">
                            <div class="agenda-tanggal">
                                <span class="tgl">20</span>
                                <span class="bln">Jul</span>
                            </div>
                            <div class="agenda-info">
                                <h4>Rapat Wali Murid</h4>
                                <span>08.00 - Aula Sekolah</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <div class="agenda-tanggal">
                                <span class="tgl">25</span>
                                <span class="bln">Jul</span>
                            </div>
                            <div class="agenda-info">
                                <h4>MPLS Siswa Baru</h4>
                                <span>07.00 - Lapangan</span>
                            </div>
                        </a>
                    </li>
                    <?php // endforeach; ?>
                </ul>
                <a href="<?= base_url('agenda') ?>" class="widget-info-more">
                    Lihat Semua <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- ============================= -->
        <!-- Galeri Kegiatan | Ekstrakurikuler -->
        <!-- ============================= -->
        <div class="section-split">
            <div>
                <div class="section-heading">
                    <h2>Galeri Kegiatan</h2>
                    <a href="<?= base_url('galeri') ?>" class="lihat-semua">
                        Lihat Semua <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="galeri-grid">
                    <?php // foreach ($galeri as $g): ?>
                    <a href="#" class="galeri-item">
                        <img src="<?= base_url('assets/images/galeri/1.jpg') ?>" alt="Kegiatan sekolah 1">
                    </a>
                    <a href="#" class="galeri-item">
                        <img src="<?= base_url('assets/images/galeri/2.jpg') ?>" alt="Kegiatan sekolah 2">
                    </a>
                    <a href="#" class="galeri-item">
                        <img src="<?= base_url('assets/images/galeri/3.jpg') ?>" alt="Kegiatan sekolah 3">
                    </a>
                    <a href="#" class="galeri-item">
                        <img src="<?= base_url('assets/images/galeri/4.jpg') ?>" alt="Kegiatan sekolah 4">
                    </a>
                    <?php // endforeach; ?>
                </div>
            </div>

            <div class="widget-ekskul">
                <div class="widget-info-header">
                    <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                    <h3>Ekstrakurikuler</h3>
                </div>
                <div class="ekskul-grid">
                    <?php // foreach ($ekskul as $e): ?>
                    <a href="#" class="ekskul-item">
                        <i class="fa-solid fa-futbol" aria-hidden="true"></i>
                        <span>Futsal</span>
                    </a>
                    <a href="#" class="ekskul-item">
                        <i class="fa-solid fa-music" aria-hidden="true"></i>
                        <span>Hadroh</span>
                    </a>
                    <a href="#" class="ekskul-item">
                        <i class="fa-solid fa-scroll" aria-hidden="true"></i>
                        <span>Pramuka</span>
                    </a>
                    <a href="#" class="ekskul-item">
                        <i class="fa-solid fa-book-quran" aria-hidden="true"></i>
                        <span>Tahfidz</span>
                    </a>
                    <a href="#" class="ekskul-item">
                        <i class="fa-solid fa-basketball" aria-hidden="true"></i>
                        <span>Basket</span>
                    </a>
                    <a href="#" class="ekskul-item">
                        <i class="fa-solid fa-drum" aria-hidden="true"></i>
                        <span>Marching Band</span>
                    </a>
                    <a href="#" class="ekskul-item">
                        <i class="fa-solid fa-palette" aria-hidden="true"></i>
                        <span>Seni Rupa</span>
                    </a>
                    <a href="#" class="ekskul-item">
                        <i class="fa-solid fa-laptop-code" aria-hidden="true"></i>
                        <span>Robotik</span>
                    </a>
                    <?php // endforeach; ?>
                </div>
                <a href="<?= base_url('ekstrakurikuler') ?>" class="widget-info-more">
                    Lihat Semua <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
        <!-- ⬆️ .section-split (Galeri | Ekskul) DITUTUP DI SINI -->

        <!-- ============================= -->
        <!-- Prestasi Sekolah -->
        <!-- ============================= -->
        <div class="prestasi-section">
            <div class="section-heading">
                <h2>Prestasi Sekolah</h2>
                <a href="<?= base_url('prestasi') ?>" class="lihat-semua">
                    Lihat Semua <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="prestasi-grid">
                <?php // foreach ($prestasi as $p): ?>
                <div class="prestasi-card">
                    <div class="prestasi-card-img">
                        <img src="<?= base_url('assets/images/prestasi/1.jpg') ?>"
                            alt="Juara 1 Lomba Barista Tingkat Provinsi">
                        <span class="prestasi-badge">Provinsi</span>
                        <div class="prestasi-card-icon"><i class="fa-solid fa-trophy"></i></div>
                    </div>
                    <div class="prestasi-card-body">
                        <h4>Juara 1 Lomba Barista Tingkat Provinsi</h4>
                        <p>Tim Tata Boga &middot; Juli 2026</p>
                    </div>
                </div>

                <div class="prestasi-card">
                    <div class="prestasi-card-img">
                        <img src="<?= base_url('assets/images/prestasi/2.jpg') ?>"
                            alt="Juara 2 LKS Rekayasa Perangkat Lunak">
                        <span class="prestasi-badge">Nasional</span>
                        <div class="prestasi-card-icon"><i class="fa-solid fa-trophy"></i></div>
                    </div>
                    <div class="prestasi-card-body">
                        <h4>Juara 2 LKS Rekayasa Perangkat Lunak</h4>
                        <p>Tim RPL &middot; Juni 2026</p>
                    </div>
                </div>

                <div class="prestasi-card">
                    <div class="prestasi-card-img">
                        <img src="<?= base_url('assets/images/prestasi/3.jpg') ?>"
                            alt="Juara 1 Lomba Debat Bahasa Inggris">
                        <span class="prestasi-badge">Kabupaten</span>
                        <div class="prestasi-card-icon"><i class="fa-solid fa-trophy"></i></div>
                    </div>
                    <div class="prestasi-card-body">
                        <h4>Juara 1 Lomba Debat Bahasa Inggris</h4>
                        <p>Tim Bahasa &middot; Mei 2026</p>
                    </div>
                </div>
                <?php // endforeach; ?>
            </div>
        </div>

        <!-- ============================= -->
        <!-- Partner Industri -->
        <!-- ============================= -->
        <div class="partner-section">
            <div class="section-heading">
                <h2>Partner Industri</h2>
                <a href="<?= base_url('partner') ?>" class="lihat-semua">
                    Lihat Semua <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="partner-info-marquee">
                <div class="partner-info-track">
                    <?php // foreach ($partner as $p): ?>
                    <a href="<?= base_url('partner/detail/pt-mitra-industri-satu') ?>" class="partner-info-card">
                        <img src="<?= base_url('assets/images/partner/foto-1.jpg') ?>" alt="PT Mitra Industri Satu">
                        <div class="partner-info-body">
                            <h4>PT Mitra Industri Satu</h4>
                            <p>Perusahaan manufaktur elektronik yang menjadi mitra magang siswa jurusan RPL dan TKJ
                                sejak 2021.</p>
                        </div>
                    </a>

                    <a href="<?= base_url('partner/detail/cv-karya-teknologi') ?>" class="partner-info-card">
                        <img src="<?= base_url('assets/images/partner/foto-2.jpg') ?>" alt="CV Karya Teknologi">
                        <div class="partner-info-body">
                            <h4>CV Karya Teknologi</h4>
                            <p>Bergerak di bidang jasa perbaikan komputer dan jaringan, rutin menerima siswa PKL setiap
                                semester.</p>
                        </div>
                    </a>

                    <a href="<?= base_url('partner/detail/pt-boga-sejahtera') ?>" class="partner-info-card">
                        <img src="<?= base_url('assets/images/partner/foto-3.jpg') ?>" alt="PT Boga Sejahtera">
                        <div class="partner-info-body">
                            <h4>PT Boga Sejahtera</h4>
                            <p>Industri kuliner skala menengah, tempat praktik kerja lapangan bagi siswa jurusan Tata
                                Boga.</p>
                        </div>
                    </a>

                    <a href="<?= base_url('partner/detail/pt-digital-nusantara') ?>" class="partner-info-card">
                        <img src="<?= base_url('assets/images/partner/foto-4.jpg') ?>" alt="PT Digital Nusantara">
                        <div class="partner-info-body">
                            <h4>PT Digital Nusantara</h4>
                            <p>Perusahaan pengembangan aplikasi yang membuka program magang berbayar untuk siswa RPL.
                            </p>
                        </div>
                    </a>

                    <a href="<?= base_url('partner/detail/cv-mandiri-perkasa') ?>" class="partner-info-card">
                        <img src="<?= base_url('assets/images/partner/foto-5.jpg') ?>" alt="CV Mandiri Perkasa">
                        <div class="partner-info-body">
                            <h4>CV Mandiri Perkasa</h4>
                            <p>Bergerak di bidang otomotif, menjadi tempat pelatihan kerja siswa jurusan Teknik
                                Kendaraan.</p>
                        </div>
                    </a>

                    <!-- duplikat set, untuk loop mulus -->
                    <a href="<?= base_url('partner/detail/pt-mitra-industri-satu') ?>" class="partner-info-card"
                        aria-hidden="true" tabindex="-1">
                        <img src="<?= base_url('assets/images/partner/foto-1.jpg') ?>" alt="">
                        <div class="partner-info-body">
                            <h4>PT Mitra Industri Satu</h4>
                            <p>Perusahaan manufaktur elektronik yang menjadi mitra magang siswa jurusan RPL dan TKJ
                                sejak 2021.</p>
                        </div>
                    </a>
                    <a href="<?= base_url('partner/detail/cv-karya-teknologi') ?>" class="partner-info-card"
                        aria-hidden="true" tabindex="-1">
                        <img src="<?= base_url('assets/images/partner/foto-2.jpg') ?>" alt="">
                        <div class="partner-info-body">
                            <h4>CV Karya Teknologi</h4>
                            <p>Bergerak di bidang jasa perbaikan komputer dan jaringan, rutin menerima siswa PKL setiap
                                semester.</p>
                        </div>
                    </a>
                    <a href="<?= base_url('partner/detail/pt-boga-sejahtera') ?>" class="partner-info-card"
                        aria-hidden="true" tabindex="-1">
                        <img src="<?= base_url('assets/images/partner/foto-3.jpg') ?>" alt="">
                        <div class="partner-info-body">
                            <h4>PT Boga Sejahtera</h4>
                            <p>Industri kuliner skala menengah, tempat praktik kerja lapangan bagi siswa jurusan Tata
                                Boga.</p>
                        </div>
                    </a>
                    <a href="<?= base_url('partner/detail/pt-digital-nusantara') ?>" class="partner-info-card"
                        aria-hidden="true" tabindex="-1">
                        <img src="<?= base_url('assets/images/partner/foto-4.jpg') ?>" alt="">
                        <div class="partner-info-body">
                            <h4>PT Digital Nusantara</h4>
                            <p>Perusahaan pengembangan aplikasi yang membuka program magang berbayar untuk siswa RPL.
                            </p>
                        </div>
                    </a>
                    <a href="<?= base_url('partner/detail/cv-mandiri-perkasa') ?>" class="partner-info-card"
                        aria-hidden="true" tabindex="-1">
                        <img src="<?= base_url('assets/images/partner/foto-5.jpg') ?>" alt="">
                        <div class="partner-info-body">
                            <h4>CV Mandiri Perkasa</h4>
                            <p>Bergerak di bidang otomotif, menjadi tempat pelatihan kerja siswa jurusan Teknik
                                Kendaraan.</p>
                        </div>
                    </a>
                    <?php // endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ============================= -->
        <!-- Peta Lokasi & Kontak Cepat -->
        <!-- ============================= -->
        <div class="lokasi-section">
            <div class="section-heading">
                <h2>Lokasi &amp; Kontak Cepat</h2>
            </div>
            <div class="lokasi-split">
                <div class="lokasi-map">
                    <iframe src="https://www.google.com/maps?q=SMK+Attaufiqiyyah&output=embed" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi SMK Attaufiqiyyah">
                    </iframe>
                </div>
                <div class="kontak-cepat">
                    <div class="kontak-item">
                        <div class="kontak-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <h4>Alamat</h4>
                            <p>Jl. Contoh No. 123, Kec. Contoh, Kab. Contoh</p>
                        </div>
                    </div>
                    <div class="kontak-item">
                        <div class="kontak-icon"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <h4>Telepon</h4>
                            <p>(021) 1234-5678</p>
                        </div>
                    </div>
                    <div class="kontak-item">
                        <div class="kontak-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div>
                            <h4>Email</h4>
                            <p>info@smkattaufiqiyyah.sch.id</p>
                        </div>
                    </div>
                    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="btn-wa-cepat">
                        <i class="fa-brands fa-whatsapp"></i> Chat via WhatsApp
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<?= $this->endSection() ?>