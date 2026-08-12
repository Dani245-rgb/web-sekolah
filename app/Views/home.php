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
                    <?php foreach ($berita_utama as $b): ?>
                        <a href="<?= base_url('berita/' . $b['slug']) ?>" class="berita-card">
                            <img src="<?= $b['gambar'] ? base_url('assets/images/berita/' . $b['gambar']) : base_url('assets/images/berita/default.jpg') ?>" alt="">
                            <div class="berita-card-body">
                                <span class="berita-kategori"><?= esc($b['kategori']) ?></span>
                                <h3><?= esc($b['judul']) ?></h3>
                                <span class="berita-meta"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($berita_utama)): ?>
                        <p>Belum ada berita.</p>
                    <?php endif; ?>
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
                    <?php foreach ($berita_terbaru as $b): ?>
                        <a href="<?= base_url('berita/' . $b['slug']) ?>" class="berita-list-item">
                            <img src="<?= $b['gambar'] ? base_url('assets/images/berita/' . $b['gambar']) : base_url('assets/images/berita/default.jpg') ?>" alt="">
                            <div class="berita-list-body">
                                <span class="berita-kategori"><?= esc($b['kategori']) ?></span>
                                <h3><?= esc($b['judul']) ?></h3>
                                <span class="berita-meta"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($berita_terbaru)): ?>
                        <p>Belum ada berita terbaru.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="widget-agenda">
                <div class="widget-info-header">
                    <i class="fa-solid fa-calendar-days"></i>
                    <h3>Agenda Sekolah</h3>
                </div>
                <ul class="agenda-list">
                    <?php foreach ($agenda as $a): ?>
                        <li>
                            <a href="#">
                                <div class="agenda-tanggal">
                                    <span class="tgl"><?= date('d', strtotime($a['tanggal'])) ?></span>
                                    <span class="bln"><?= date('M', strtotime($a['tanggal'])) ?></span>
                                </div>
                                <div class="agenda-info">
                                    <h4><?= esc($a['judul']) ?></h4>
                                    <span><?= esc($a['waktu']) ?><?= $a['lokasi'] ? ' - ' . esc($a['lokasi']) : '' ?></span>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($agenda)): ?>
                        <li><span class="text-muted">Belum ada agenda mendatang.</span></li>
                    <?php endif; ?>
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
                    <?php foreach ($galeri as $g): ?>
                        <a href="<?= base_url('galeri#' . $g['id']) ?>" class="galeri-item">
                            <img src="<?= base_url('uploads/galeri/' . $g['foto']) ?>" alt="<?= esc($g['judul']) ?>">
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($galeri)): ?>
                        <p>Belum ada foto galeri.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="widget-ekskul">
                <div class="widget-info-header">
                    <i class="fa-solid fa-people-group" aria-hidden="true"></i>
                    <h3>Ekstrakurikuler</h3>
                </div>
                <div class="ekskul-grid">
                    <?php foreach ($ekskul as $e): ?>
                        <a href="#" class="ekskul-item">
                            <img src="<?= base_url('uploads/ekstrakurikuler/' . $e['foto']) ?>" alt="<?= esc($e['nama']) ?>" style="width:32px;height:32px;object-fit:cover;border-radius:6px;">
                            <span><?= esc($e['nama']) ?></span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($ekskul)): ?>
                        <p class="text-muted">Belum ada ekstrakurikuler.</p>
                    <?php endif; ?>
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
                <?php foreach ($prestasi as $p): ?>
                    <div class="prestasi-card">
                        <div class="prestasi-card-img">
                            <img src="<?= base_url('uploads/prestasi/' . $p['foto']) ?>"
                                alt="<?= esc($p['judul']) ?>">
                            <span class="prestasi-badge"><?= esc($p['tingkat']) ?></span>
                            <div class="prestasi-card-icon"><i class="fa-solid fa-trophy"></i></div>
                        </div>
                        <div class="prestasi-card-body">
                            <h4><?= esc($p['judul']) ?></h4>
                            <p><?= esc($p['tim']) ?> &middot; <?= date('F Y', strtotime($p['tanggal'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($prestasi)): ?>
                    <p>Belum ada prestasi.</p>
                <?php endif; ?>
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
                        <?php foreach ($partner as $p): ?>
                            <a href="<?= base_url('partner/detail/' . $p['slug']) ?>" class="partner-info-card">
                                <img src="<?= base_url('uploads/partner/' . $p['foto']) ?>" alt="<?= esc($p['nama']) ?>">
                                <div class="partner-info-body">
                                    <h4><?= esc($p['nama']) ?></h4>
                                    <p><?= esc(character_limiter($p['deskripsi'], 100)) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                        <?php // duplikat untuk efek marquee tak terputus, kalau perlu bisa foreach lagi 
                        ?>
                        <?php foreach ($partner as $p): ?>
                            <a href="<?= base_url('partner/detail/' . $p['slug']) ?>" class="partner-info-card" aria-hidden="true" tabindex="-1">
                                <img src="<?= base_url('uploads/partner/' . $p['foto']) ?>" alt="">
                                <div class="partner-info-body">
                                    <h4><?= esc($p['nama']) ?></h4>
                                    <p><?= esc(character_limiter($p['deskripsi'], 100)) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
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