<?= $this->extend('layouts/template') ?>
<?= $this->section('content') ?>

<main class="content">
    <div class="container">
        <span class="berita-kategori"><?= esc($berita['kategori']) ?></span>
        <h1><?= esc($berita['judul']) ?></h1>
        <span class="berita-meta"><?= date('d F Y', strtotime($berita['tanggal_publish'])) ?></span>

        <?php if ($berita['gambar']): ?>
        <img src="<?= base_url('assets/images/berita/' . $berita['gambar']) ?>" style="width:100%; margin:16px 0;" alt="">
        <?php endif; ?>

        <div class="berita-konten">
            <?= nl2br(esc($berita['konten'])) ?>
        </div>

        <?php if (!empty($terkait)): ?>
        <h3 style="margin-top:32px;">Berita Terkait</h3>
        <div class="berita-utama-list">
            <?php foreach ($terkait as $t): ?>
            <a href="<?= base_url('berita/' . $t['slug']) ?>" class="berita-card">
                <img src="<?= $t['gambar'] ? base_url('assets/images/berita/' . $t['gambar']) : base_url('assets/images/berita/default.jpg') ?>" alt="">
                <div class="berita-card-body">
                    <h3><?= esc($t['judul']) ?></h3>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?= $this->endSection() ?>