<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <nav class="breadcrumb" aria-label="breadcrumb">
            <a href="<?= base_url('/') ?>">Beranda</a>
            <span class="sep">/</span>
            <span class="current">Hasil Pencarian</span>
        </nav>

        <div class="section-heading">
            <h2>Hasil Pencarian untuk "<?= esc($keyword) ?>"</h2>
        </div>

        <?php if ($keyword === ''): ?>
            <p>Masukkan kata kunci untuk mencari.</p>
        <?php elseif ($totalHasil === 0): ?>
            <p>Tidak ada hasil ditemukan untuk "<?= esc($keyword) ?>".</p>
        <?php else: ?>

            <p style="color:#666; margin-bottom:20px;"><?= $totalHasil ?> hasil ditemukan</p>

            <?php if (!empty($berita)): ?>
                <h3>Berita</h3>
                <ul class="search-result-list">
                    <?php foreach ($berita as $item): ?>
                        <li>
                            <a href="<?= base_url('berita/' . $item['slug']) ?>"><?= esc($item['judul']) ?></a>
                            <p><?= esc($item['ringkasan']) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($pengumuman)): ?>
                <h3>Pengumuman</h3>
                <ul class="search-result-list">
                    <?php foreach ($pengumuman as $item): ?>
                        <li>
                            <a href="<?= base_url('pengumuman/' . $item['slug']) ?>"><?= esc($item['judul']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($prestasi)): ?>
                <h3>Prestasi</h3>
                <ul class="search-result-list">
                    <?php foreach ($prestasi as $item): ?>
                        <li>
                            <a href="<?= base_url('prestasi') ?>"><?= esc($item['judul']) ?></a>
                            <?php if (!empty($item['tim'])): ?> — <?= esc($item['tim']) ?><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($agenda)): ?>
                <h3>Agenda</h3>
                <ul class="search-result-list">
                    <?php foreach ($agenda as $item): ?>
                        <li>
                            <a href="<?= base_url('agenda') ?>"><?= esc($item['judul']) ?></a>
                            — <?= date('d F Y', strtotime($item['tanggal'])) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>