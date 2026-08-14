<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Galeri Sekolah</h2>
        </div>

        <!-- Filter kategori -->
        <div class="galeri-filter">
            <a href="<?= base_url('galeri') ?>"
                class="galeri-filter-btn <?= $kategori === null ? 'active' : '' ?>">Semua</a>
            <?php $kategoriList = ['Kegiatan', 'Fasilitas', 'Prestasi', 'Lainnya']; ?>
            <?php foreach ($kategoriList as $k): ?>
                <a href="<?= base_url('galeri?kategori=' . $k) ?>"
                    class="galeri-filter-btn <?= $kategori === $k ? 'active' : '' ?>"><?= $k ?></a>
            <?php endforeach; ?>
        </div>

        <div class="galeri-grid">
            <?php foreach ($galeri as $g): ?>
                <a href="<?= base_url('uploads/galeri/' . $g['foto']) ?>" target="_blank" class="galeri-item" title="<?= esc($g['judul']) ?>">
                    <img src="<?= base_url('uploads/galeri/' . $g['foto']) ?>" alt="<?= esc($g['judul']) ?>">
                </a>
            <?php endforeach; ?>

            <?php if (empty($galeri)): ?>
                <div class="empty-state">
                    <i class="bi bi-images"></i>
                    <p>Belum ada foto <?= $kategori ? 'untuk kategori ' . esc($kategori) : '' ?>.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?= $this->endSection() ?>