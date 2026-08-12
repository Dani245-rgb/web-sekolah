<?= $this->extend('layouts/template') ?>
<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Semua Berita</h2>
        </div>

        <div class="berita-utama-list">
            <?php foreach ($beritaList as $b): ?>
            <a href="<?= base_url('berita/' . $b['slug']) ?>" class="berita-card">
                <img src="<?= $b['gambar'] ? base_url('assets/images/berita/' . $b['gambar']) : base_url('assets/images/berita/default.jpg') ?>" alt="<?= esc($b['judul']) ?>">
                <div class="berita-card-body">
                    <span class="berita-kategori"><?= esc($b['kategori']) ?></span>
                    <h3><?= esc($b['judul']) ?></h3>
                    <span class="berita-meta"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                </div>
            </a>
            <?php endforeach; ?>

            <?php if (empty($beritaList)): ?>
            <p>Belum ada berita.</p>
            <?php endif; ?>
        </div>

        <?php if ($pager->getPageCount() > 1): ?>
        <div class="pagination-wrap" style="margin-top: 24px;">
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>