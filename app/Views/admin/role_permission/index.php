<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<h4 class="mb-3">Role & Permission</h4>
<p class="text-muted">Ringkasan hak akses tiap role dalam sistem. Halaman ini bersifat informatif — untuk mengubah akses, sesuaikan langsung di konfigurasi routing sistem.</p>

<?php foreach ($roles as $role): ?>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="mb-1"><?= esc($role['nama']) ?></h5>
        <p class="text-muted mb-3"><?= esc($role['desc']) ?></p>

        <?php foreach ($role['grup'] as $namaGrup => $modul): ?>
            <strong><?= esc($namaGrup) ?></strong>
            <ul class="mb-3">
                <?php foreach ($modul as $m): ?>
                    <li><?= esc($m) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?= $this->endSection() ?>