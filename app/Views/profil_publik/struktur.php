<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Struktur Organisasi</h2>
        </div>

        <?php foreach ($organisasi as $org): ?>
        <div style="margin-bottom:40px;">
            <h3><?= esc($org['nama']) ?></h3>
            <?php if ($org['deskripsi']): ?>
            <p style="color:#64748b;"><?= esc($org['deskripsi']) ?></p>
            <?php endif; ?>

            <div class="row">
                <?php foreach ($org['anggota'] as $a): ?>
                <div class="col-md-3 mb-4" style="width:22%;display:inline-block;vertical-align:top;margin-right:1%;text-align:center;">
                    <img src="<?= $a['foto'] ? base_url('uploads/organisasi/' . $a['foto']) : base_url('assets/images/default-avatar.png') ?>"
                         style="width:100px;height:100px;object-fit:cover;border-radius:50%;margin:0 auto 8px;">
                    <h6 style="margin:0;"><?= esc($a['nama']) ?></h6>
                    <p style="color:#64748b;margin:0;"><?= esc($a['jabatan']) ?></p>
                </div>
                <?php endforeach; ?>

                <?php if (empty($org['anggota'])): ?>
                <p class="text-muted">Belum ada anggota.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($organisasi)): ?>
        <p>Belum ada data organisasi.</p>
        <?php endif; ?>

    </div>
</main>

<?= $this->endSection() ?>