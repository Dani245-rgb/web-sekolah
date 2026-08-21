<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Ekstrakurikuler</h2>
        </div>

        <div class="galeri-grid">
            <?php foreach ($ekskul as $e): ?>
            <a href="#" class="galeri-item">
                <?php if (!empty($e['foto'])): ?>
                    <img src="<?= base_url('uploads/ekstrakurikuler/' . $e['foto']) ?>" alt="<?= esc($e['nama']) ?>">
                <?php else: ?>
                    <div class="galeri-item-placeholder"><?= esc($e['nama']) ?></div>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>

            <?php if (empty($ekskul)): ?>
            <p>Belum ada data ekstrakurikuler.</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?= $this->endSection() ?>