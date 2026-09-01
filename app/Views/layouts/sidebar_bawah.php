<aside class="sidebar-bawah">
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