<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">
        <div class="section-heading">
            <h2>Industri Mitra</h2>
        </div>

        <div class="partner-info-track" style="flex-wrap: wrap;">
            <?php foreach ($partner as $p): ?>
            <a href="<?= base_url('partner/detail/' . $p['slug']) ?>" class="partner-info-card">
                <?php if (!empty($p['foto'])): ?>
                    <img src="<?= base_url('uploads/partner/' . $p['foto']) ?>" alt="<?= esc($p['nama']) ?>">
                <?php else: ?>
                    <div class="galeri-item-placeholder"><?= esc($p['nama']) ?></div>
                <?php endif; ?>
                <div class="partner-info-body">
                    <h4><?= esc($p['nama']) ?></h4>
                    <p><?= esc(character_limiter($p['deskripsi'] ?? '', 100)) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
            <?php if (empty($partner)): ?>
            <p>Belum ada data partner.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?= $this->endSection() ?>