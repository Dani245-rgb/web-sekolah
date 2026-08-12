<aside class="sidebar">

    <!-- Widget Informasi Sekolah (ganti dari Pengumuman) -->
    <div class="widget-info">
        <div class="widget-info-header">
            <i class="fa-solid fa-bullhorn"></i>
            <h3>Informasi Sekolah</h3>
        </div>


        <ul class="info-list">
            <?php foreach ($informasi as $i): ?>
                <li>
                    <a href="<?= base_url('pengumuman/' . $i['slug']) ?>">
                        <span class="info-dot"></span>
                        <?= esc($i['judul']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($informasi)): ?>
                <li><span class="text-muted">Belum ada informasi.</span></li>
            <?php endif; ?>
        </ul>

        <a href="<?= base_url('pengumuman') ?>" class="widget-info-more">
            Lihat Semua <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <!-- Widget Tab Berita: Terbaru | Populer | Hits -->
    <?php
    $beritaModelSidebar = new \App\Models\BeritaModel();
    $beritaTerbaruWidget = $beritaModelSidebar->getTerbaru(5, 0);
    $beritaPopulerWidget = $beritaModelSidebar->getPopuler(5);
    $beritaHitsWidget    = $beritaModelSidebar->getHits(5);
    ?>
    <div class="berita-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" data-tab="terbaru">Terbaru</button>
            <button class="tab-btn" data-tab="populer">Populer</button>
            <button class="tab-btn" data-tab="hits">Hits</button>
        </div>

        <div class="tab-content active" id="terbaru">
            <?php foreach ($beritaTerbaruWidget as $b): ?>
                <a href="<?= base_url('berita/' . $b['slug']) ?>" class="tab-item">
                    <span class="tab-item-title"><?= esc($b['judul']) ?></span>
                    <span class="tab-item-date"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                </a>
            <?php endforeach; ?>
            <?php if (empty($beritaTerbaruWidget)): ?>
                <p style="padding:12px;">Belum ada berita.</p>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="populer">
            <?php foreach ($beritaPopulerWidget as $b): ?>
                <a href="<?= base_url('berita/' . $b['slug']) ?>" class="tab-item">
                    <span class="tab-item-title"><?= esc($b['judul']) ?></span>
                    <span class="tab-item-date"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                </a>
            <?php endforeach; ?>
            <?php if (empty($beritaPopulerWidget)): ?>
                <p style="padding:12px;">Belum ada berita.</p>
            <?php endif; ?>
        </div>

        <div class="tab-content" id="hits">
            <?php foreach ($beritaHitsWidget as $b): ?>
                <a href="<?= base_url('berita/' . $b['slug']) ?>" class="tab-item">
                    <span class="tab-item-title"><?= esc($b['judul']) ?></span>
                    <span class="tab-item-date"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                </a>
            <?php endforeach; ?>
            <?php if (empty($beritaHitsWidget)): ?>
                <p style="padding:12px;">Belum ada berita.</p>
            <?php endif; ?>
        </div>
    </div>

</aside>