<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Kepala Sekolah</h2>
        </div>

        <?php if ($item && $item['nama']): ?>
        <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
            <?php if ($item['foto']): ?>
            <img src="<?= base_url('uploads/profil/' . $item['foto']) ?>" alt="<?= esc($item['nama']) ?>"
                 style="width:200px;height:200px;object-fit:cover;border-radius:12px;">
            <?php endif; ?>
            <div style="flex:1;min-width:250px;">
                <h3 style="margin:0 0 4px;"><?= esc($item['nama']) ?></h3>
                <p style="color:#64748b;margin:0 0 16px;"><?= esc($item['jabatan']) ?></p>
                <div style="line-height:1.8;"><?= nl2br(esc($item['konten'])) ?></div>
            </div>
        </div>
        <?php else: ?>
        <p>Data belum tersedia.</p>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>