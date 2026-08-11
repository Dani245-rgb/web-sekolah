<?= $this->extend('layouts/template') ?>
<?= $this->section('content') ?>

<main class="content">
    <div class="container">
        <h2>Semua Berita</h2>
        <div class="berita-utama-list">
            <?php foreach ($beritaList as $b): ?>
            <a href="<?= base_url('berita/' . $b['slug']) ?>" class="berita-card">
                <img src="<?= $b['gambar'] ? base_url('assets/images/berita/' . $b['gambar']) : base_url('assets/images/berita/default.jpg') ?>" alt="">
                <div class="berita-card-body">
                    <span class="berita-kategori"><?= esc($b['kategori']) ?></span>
                    <h3><?= esc($b['judul']) ?></h3>
                    <span class="berita-meta"><?= date('d F Y', strtotime($b['tanggal_publish'])) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?= $pager->links() ?>
    </div>
</main>

<?= $this->endSection() ?>