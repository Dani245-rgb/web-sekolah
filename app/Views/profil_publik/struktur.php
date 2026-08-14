<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Struktur Organisasi</h2>
        </div>

        <?php foreach ($organisasi as $org): ?>
        <div class="organisasi-block">
            <h3><?= esc($org['nama']) ?></h3>
            <?php if ($org['deskripsi']): ?>
            <p class="organisasi-desc"><?= esc($org['deskripsi']) ?></p>
            <?php endif; ?>

            <div class="organisasi-grid">
                <?php foreach ($org['anggota'] as $a): ?>
                <div class="organisasi-card">
                    <img src="<?= $a['foto'] ? base_url('uploads/organisasi/' . $a['foto']) : base_url('assets/images/default-avatar.png') ?>" alt="<?= esc($a['nama']) ?>">
                    <h6><?= esc($a['nama']) ?></h6>
                    <p><?= esc($a['jabatan']) ?></p>
                </div>
                <?php endforeach; ?>

                <?php if (empty($org['anggota'])): ?>
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <p>Belum ada anggota.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($organisasi)): ?>
        <div class="empty-state">
            <i class="bi bi-diagram-3"></i>
            <p>Belum ada data organisasi.</p>
        </div>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>