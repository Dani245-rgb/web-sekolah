<?= $this->extend('layouts/template') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/bk-artikel-publik.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <p><a href="<?= base_url('bk/' . str_replace('_', '-', $item['kategori'])) ?>">
            <i class="bi bi-arrow-left"></i> Kembali ke <?= esc($label) ?>
        </a></p>

        <div class="section-heading">
            <h2><?= esc($item['judul']) ?></h2>
        </div>

        <?php if (!empty($item['foto'])): ?>
        <img src="<?= base_url('uploads/bk_artikel/' . esc($item['foto'], 'attr')) ?>"
             alt="<?= esc($item['judul']) ?>"
             style="width:100%;max-height:360px;object-fit:cover;border-radius:12px;margin-bottom:20px;">
        <?php endif; ?>

        <?php if (!empty($item['deadline'])): ?>
        <div class="bk-artikel-deadline" style="margin-bottom:16px;">
            <i class="bi bi-clock"></i> Deadline: <?= date('d M Y', strtotime($item['deadline'])) ?>
        </div>
        <?php endif; ?>

        <div style="line-height:1.8;"><?= nl2br(esc($item['konten'])) ?></div>

        <?php if (!empty($item['link_eksternal'])): ?>
        <p style="margin-top:24px;">
            <a href="<?= esc($item['link_eksternal'], 'attr') ?>" target="_blank" rel="noopener"
               class="btn btn-primary">
                Buka Link Terkait <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </p>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>