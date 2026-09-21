<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Guru & Staff</h2>
        </div>

        <div class="guru-grid">
            <?php foreach ($guru as $g): ?>
            <div class="guru-card">
                <img src="<?= $g['foto'] ? base_url('uploads/guru/' . $g['foto']) : base_url('assets/images/default-avatar.png') ?>"
                     alt="<?= esc($g['nama']) ?>"
                     class="guru-card-foto">
                <h5 class="guru-card-nama"><?= esc($g['nama']) ?></h5>
                <p class="guru-card-jabatan"><?= esc($g['jabatan']) ?></p>
            </div>
    <?php endforeach; ?>

            <?php if (empty($guru)): ?>
            <p>Belum ada data guru & staff.</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?= $this->endSection() ?>