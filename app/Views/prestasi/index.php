<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Prestasi Sekolah</h2>
        </div>

        <div class="prestasi-grid">
            <?php foreach ($prestasi as $p): ?>
            <div class="prestasi-card">
                <div class="prestasi-card-img">
                    <img src="<?= base_url('uploads/prestasi/' . $p['foto']) ?>" alt="<?= esc($p['judul']) ?>">
                    <span class="prestasi-badge"><?= esc($p['tingkat']) ?></span>
                    <div class="prestasi-card-icon"><i class="fa-solid fa-trophy"></i></div>
                </div>
                <div class="prestasi-card-body">
                    <h4><?= esc($p['judul']) ?></h4>
                    <p><?= esc($p['tim']) ?> &middot; <?= date('F Y', strtotime($p['tanggal'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($prestasi)): ?>
            <p>Belum ada prestasi.</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?= $this->endSection() ?>