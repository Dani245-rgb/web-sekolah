<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Pengumuman</h2>
        </div>

        <div class="berita-list">
            <?php foreach ($pengumuman as $p): ?>
            <a href="<?= base_url('pengumuman/' . $p['slug']) ?>" class="berita-list-item">
                <div class="berita-list-body">
                    <h3><?= esc($p['judul']) ?></h3>
                    <span class="berita-meta"><?= date('d F Y', strtotime($p['tanggal_publish'])) ?></span>
                </div>
            </a>
            <?php endforeach; ?>

            <?php if (empty($pengumuman)): ?>
            <p>Belum ada pengumuman.</p>
            <?php endif; ?>
        </div>

        <?php if (isset($pager)): ?>
        <div class="pagination-wrap">
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>