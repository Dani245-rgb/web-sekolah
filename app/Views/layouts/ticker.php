<div class="ticker-bar">
    <div class="container ticker-bar-inner">
        <?php if (!empty($topTags)): ?>
        <div class="top-tags">
            <span class="top-tags-label"># Top Tags</span>
            <?php foreach ($topTags as $tag): ?>
                <a href="<?= base_url('berita?tag=' . urlencode($tag)) ?>"><?= esc($tag) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($tickerBerita)): ?>
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
        <?php endif; ?>
    </div>
</div>