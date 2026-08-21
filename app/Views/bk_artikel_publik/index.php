<?= $this->extend('layouts/template') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/bk-artikel-publik.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2><?= esc($label) ?></h2>
        </div>

        <?php if (!empty($artikelList)): ?>
        <div class="bk-artikel-grid">
            <?php foreach ($artikelList as $a): ?>
            <a href="<?= base_url('bk/artikel/' . $a['slug']) ?>" class="bk-artikel-card">
                <?php if (!empty($a['foto'])): ?>
                <img src="<?= base_url('uploads/bk_artikel/' . esc($a['foto'], 'attr')) ?>"
                     alt="<?= esc($a['judul']) ?>" class="bk-artikel-card-img">
                <?php else: ?>
                <div class="bk-artikel-card-img bk-artikel-card-img-kosong">
                    <i class="bi bi-file-text"></i>
                </div>
                <?php endif; ?>

                <div class="bk-artikel-card-body">
                    <h3><?= esc($a['judul']) ?></h3>
                    <?php if (!empty($a['konten'])): ?>
                    <p><?= esc(mb_strimwidth(strip_tags($a['konten']), 0, 110, '...')) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($a['deadline'])): ?>
                    <span class="bk-artikel-deadline">
                        <i class="bi bi-clock"></i> Deadline: <?= date('d M Y', strtotime($a['deadline'])) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="text-align:center;color:#64748b;padding:60px 0;">Belum ada artikel untuk kategori ini.</p>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>