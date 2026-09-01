<aside class="sidebar-atas">
    <div class="widget-info">
        <div class="widget-info-header">
            <i class="bi bi-megaphone-fill"></i>
            <h3>Informasi Sekolah</h3>
        </div>
        <ul class="info-list">
            <?php foreach (($informasi ?? []) as $i): ?>
                <li>
                    <a href="<?= base_url('pengumuman/' . $i['slug']) ?>">
                        <span class="info-icon"><i class="bi bi-file-earmark-text"></i></span>
                        <span class="info-text"><?= esc($i['judul']) ?></span>
                        <i class="bi bi-chevron-right info-arrow"></i>
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($informasi)): ?>
                <li class="info-empty">
                    <i class="bi bi-inbox"></i>
                    <span class="text-muted">Belum ada informasi.</span>
                </li>
            <?php endif; ?>
        </ul>
        <a href="<?= base_url('pengumuman') ?>" class="widget-info-more">
            Lihat Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</aside>