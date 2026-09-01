<?php if (!empty($tickerBerita)): ?>
<div class="ticker-bar">
    <div class="container ticker-bar-inner">
        <div class="berita-ticker">
            <span class="berita-ticker-label"><i class="bi bi-broadcast"></i> Berita Terbaru</span>
            <div class="berita-ticker-track-wrap">
                <div class="berita-ticker-track">
                    <?php foreach ($tickerBerita as $t): ?>
                        <a href="<?= base_url('berita/' . $t['slug']) ?>"><?= esc($t['judul']) ?></a>
                    <?php endforeach; ?>
                    <?php foreach ($tickerBerita as $t): ?>
                        <a href="<?= base_url('berita/' . $t['slug']) ?>" aria-hidden="true" tabindex="-1"><?= esc($t['judul']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>