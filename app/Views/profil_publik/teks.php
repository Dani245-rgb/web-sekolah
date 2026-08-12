<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">
        <?php if ($item && $item['konten']): ?>
        <div class="section-heading">
            <h2><?= esc($item['judul']) ?></h2>
        </div>
        <div class="berita-detail-body" style="line-height:1.8;">
            <?= nl2br(esc($item['konten'])) ?>
        </div>
        <?php else: ?>
        <p>Konten belum tersedia.</p>
        <?php endif; ?>
    </div>
</main>

<?= $this->endSection() ?>