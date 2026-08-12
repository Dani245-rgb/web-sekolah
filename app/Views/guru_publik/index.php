<?= $this->extend('layouts/template') ?>

<?= $this->section('content') ?>

<main class="content">
    <div class="container">

        <div class="section-heading">
            <h2>Guru & Staff</h2>
        </div>

        <div class="row">
            <?php foreach ($guru as $g): ?>
            <div class="col-md-3 mb-4" style="width:23%;display:inline-block;vertical-align:top;margin-right:1%;">
                <div class="card" style="text-align:center;padding:16px;">
                    <img src="<?= $g['foto'] ? base_url('uploads/guru/' . $g['foto']) : base_url('assets/images/default-avatar.png') ?>"
                         alt="<?= esc($g['nama']) ?>"
                         style="width:120px;height:120px;object-fit:cover;border-radius:50%;margin:0 auto 12px;">
                    <h5 style="margin:0 0 4px;"><?= esc($g['nama']) ?></h5>
                    <p style="color:#64748b;margin:0;"><?= esc($g['jabatan']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($guru)): ?>
            <p>Belum ada data guru & staff.</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?= $this->endSection() ?>