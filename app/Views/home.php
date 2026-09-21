<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="content-layout">

            <!-- Hero -->
            <div class="hero" data-reveal>
                <?php if (!empty($berita_hero)): ?>
                    <?php foreach ($berita_hero as $i => $bh): ?>
                        <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>">
                            <img src="<?= $bh['gambar'] ? base_url('assets/images/berita/' . $bh['gambar']) : base_url('assets/images/hero/hero1.jpg') ?>" alt="<?= esc($bh['judul']) ?>">
                            <div class="hero-overlay">
                                <h1><?= esc($bh['judul']) ?></h1>
                                <p><?= esc($bh['ringkasan'] ?? '') ?></p>
                                <a href="<?= base_url('berita/' . $bh['slug']) ?>" class="btn-hero-cta">Baca Selengkapnya <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="hero-slide active">
                        <img src="<?= base_url('assets/images/hero/hero1.jpg') ?>" alt="Hero SMK Attaufiqiyyah">
                        <div class="hero-overlay">
                            <h1>Membentuk Generasi Kompeten & Berakhlak Mulia</h1>
                            <p>Sekolah Menengah Kejuruan unggulan dengan fasilitas modern dan mitra industri terpercaya.</p>
                            <a href="<?= base_url('ppdb') ?>" class="btn-hero-cta">Daftar PPDB Sekarang <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Widget Informasi Sekolah -->
            <?= $this->include('layouts/sidebar_atas') ?>

            <!-- Berita Utama -->
            <div class="main-berita">
                <div class="section-heading" style="margin-top: 24px;">
                    <h2>Berita Utama</h2>
                    <a href="<?= base_url('berita') ?>" class="lihat-semua">
                        Lihat Semua <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="berita-utama-list">
                    <?php foreach (array_slice($berita_utama, 0, 4) as $i => $b): ?>
                        <a href="<?= base_url('berita/' . $b['slug']) ?>" class="berita-card" data-reveal data-reveal-delay="<?= ($i % 6) + 1 ?>">
                            <img src="<?= $b['gambar'] ? base_url('assets/images/berita/' . $b['gambar']) : base_url('assets/images/berita/default.jpg') ?>" alt="">
                            <div class="berita-card-body">
                                <span class="berita-kategori"><?= esc($b['kategori']) ?></span>
                                <h3><?= esc($b['judul']) ?></h3>
                                <span class="berita-meta"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($berita_utama)): ?>
                        <div class="empty-state"><i class="bi bi-newspaper"></i>
                            <p>Belum ada berita utama saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Widget Tab Berita -->
            <?= $this->include('layouts/sidebar_bawah') ?>

            <!-- Berita Terbaru -->
            <div class="berita-terbaru-block">
                <div class="section-heading">
                    <h2>Berita Terbaru</h2>
                    <a href="<?= base_url('berita') ?>" class="lihat-semua">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="berita-terbaru-grid">
                    <?php foreach (array_slice($berita_terbaru, 0, 6) as $i => $b): ?>
                        <a href="<?= base_url('berita/' . $b['slug']) ?>" class="berita-card" data-reveal data-reveal-delay="<?= ($i % 6) + 1 ?>">
                            <img src="<?= $b['gambar'] ? base_url('assets/images/berita/' . $b['gambar']) : base_url('assets/images/berita/default.jpg') ?>" alt="">
                            <div class="berita-card-body">
                                <span class="berita-kategori"><?= esc($b['kategori']) ?></span>
                                <h3><?= esc($b['judul']) ?></h3>
                                <span class="berita-meta"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($berita_terbaru)): ?>
                        <div class="empty-state"><i class="bi bi-newspaper"></i>
                            <p>Belum ada berita terbaru.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Widget Agenda Sekolah -->
            <div class="widget-agenda" data-reveal>
                <div class="widget-info-header">
                    <i class="bi bi-calendar-event-fill"></i>
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
                <a href="<?= base_url('agenda') ?>" class="widget-info-more">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>

            <!-- Galeri Kegiatan -->
            <div class="galeri-block">
                <div class="section-heading">
                    <h2>Galeri Kegiatan</h2>
                    <a href="<?= base_url('galeri') ?>" class="lihat-semua">Lihat Semua <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="galeri-grid">
                    <?php foreach ($galeri as $i => $g): ?>
                        <a href="<?= base_url('galeri#' . $g['id']) ?>" class="galeri-item" data-reveal data-reveal-delay="<?= ($i % 6) + 1 ?>">
                            <?php if (!empty($g['foto'])): ?>
                                <img src="<?= base_url('uploads/galeri/' . $g['foto']) ?>" alt="<?= esc($g['judul']) ?>">
                            <?php else: ?>
                                <div class="galeri-item-placeholder"><?= esc($g['judul']) ?></div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($galeri)): ?>
                        <div class="empty-state"><i class="bi bi-images"></i>
                            <p>Belum ada foto galeri.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Widget Ekstrakurikuler -->
            <div class="widget-ekskul" data-reveal>
                <div class="widget-info-header">
                    <i class="bi bi-people-fill" aria-hidden="true"></i>
                    <h3>Ekstrakurikuler</h3>
                </div>
                <div class="ekskul-grid">
                    <?php foreach ($ekskul as $e): ?>
                        <a href="#" class="ekskul-item">
                            <?php if (!empty($e['foto'])): ?>
                                <img src="<?= base_url('uploads/ekstrakurikuler/' . $e['foto']) ?>" alt="<?= esc($e['nama']) ?>" style="width:32px;height:32px;object-fit:cover;border-radius:6px;">
                            <?php endif; ?>
                            <span><?= esc($e['nama']) ?></span>
                        </a>
                    <?php endforeach; ?>
                    <?php if (empty($ekskul)): ?>
                        <div class="empty-state"><i class="bi bi-people"></i>
                            <p>Belum ada ekstrakurikuler.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="<?= base_url('ekstrakurikuler') ?>" class="widget-info-more">Lihat Semua <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>

            <!-- Prestasi Sekolah -->
            <div class="prestasi-block">
                <div class="section-heading">
                    <h2>Prestasi Sekolah</h2>
                    <a href="<?= base_url('prestasi') ?>" class="lihat-semua">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="prestasi-grid">
                    <?php foreach ($prestasi as $i => $p): ?>
                        <div class="prestasi-card" data-reveal data-reveal-delay="<?= ($i % 6) + 1 ?>">
                            <div class="prestasi-card-img">
                                <?php if (!empty($p['foto'])): ?>
                                    <img src="<?= base_url('uploads/prestasi/' . $p['foto']) ?>" alt="<?= esc($p['judul']) ?>">
                                <?php else: ?>
                                    <div class="galeri-item-placeholder"><?= esc($p['judul']) ?></div>
                                <?php endif; ?>
                                <span class="prestasi-badge"><?= esc($p['tingkat']) ?></span>
                                <div class="prestasi-card-icon"><i class="bi bi-trophy-fill"></i></div>
                            </div>
                            <div class="prestasi-card-body">
                                <h4><?= esc($p['judul']) ?></h4>
                                <p><?= esc($p['tim']) ?> &middot; <?= date('F Y', strtotime($p['tanggal'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($prestasi)): ?>
                        <div class="empty-state"><i class="bi bi-trophy"></i>
                            <p>Belum ada prestasi.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <!-- ⬆️ .content-layout DITUTUP DI SINI, sekarang isinya 9 blok sekaligus -->

        <!-- Partner Industri -->
        <div class="partner-section" data-reveal>
            <div class="section-heading">
                <h2>Partner Industri</h2>
                <a href="<?= base_url('partner') ?>" class="lihat-semua">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="partner-info-marquee">
                <div class="partner-info-track">
                    <?php foreach ($partner as $p): ?>
                        <a href="<?= base_url('partner/detail/' . $p['slug']) ?>" class="partner-info-card">
                            <?php if (!empty($p['foto'])): ?>
                                <img src="<?= base_url('uploads/partner/' . $p['foto']) ?>" alt="<?= esc($p['nama']) ?>">
                            <?php endif; ?>
                            <div class="partner-info-body">
                                <h4><?= esc($p['nama']) ?></h4>
                                <p><?= esc(character_limiter($p['deskripsi'] ?? '', 100)) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <?php // duplikat untuk efek marquee tak terputus 
                    ?>
                    <?php foreach ($partner as $p): ?>
                        <a href="<?= base_url('partner/detail/' . $p['slug']) ?>" class="partner-info-card" aria-hidden="true" tabindex="-1">
                            <?php if (!empty($p['foto'])): ?>
                                <img src="<?= base_url('uploads/partner/' . $p['foto']) ?>" alt="">
                            <?php endif; ?>
                            <div class="partner-info-body">
                                <h4><?= esc($p['nama']) ?></h4>
                                <p><?= esc(character_limiter($p['deskripsi'] ?? '', 100)) ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Peta Lokasi & Kontak Cepat -->
            <div class="lokasi-section" data-reveal>
                <div class="section-heading">
                    <h2>Lokasi &amp; Kontak Cepat</h2>
                </div>
                <div class="lokasi-split">
                    <div class="lokasi-map">
                        <iframe src="https://www.google.com/maps?q=SMK+Attaufiqiyyah&output=embed" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi SMK Attaufiqiyyah"></iframe>
                    </div>
                    <div class="kontak-cepat">
                        <div class="kontak-item">
                            <div class="kontak-icon"><i class="bi bi-geo-alt-fill"></i></div>
                            <div>
                                <h4>Alamat</h4>
                                <p>Jl. Raya Serang-Pandeglang KM 14, Baros, Kabupaten Serang, Banten</p>
                            </div>
                        </div>
                        <div class="kontak-item">
                            <div class="kontak-icon"><i class="bi bi-telephone-fill"></i></div>
                            <div>
                                <h4>Telepon</h4>
                                <p>0821-1266-7568</p>
                            </div>
                        </div>
                        <div class="kontak-item">
                            <div class="kontak-icon"><i class="bi bi-envelope-fill"></i></div>
                            <div>
                                <h4>Email</h4>
                                <p>smksmaattaufiqiyyah@gmail.com</p>
                            </div>
                        </div>
                        <a href="https://wa.me/6282112667568" target="_blank" rel="noopener" class="btn-wa-cepat">
                            <i class="bi bi-whatsapp"></i> Chat via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
</main>

<?= $this->endSection() ?>