<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<h4 style="margin-bottom:8px;">Role & Permission</h4>
<p style="color:#7c8a9c;font-size:13px;margin-bottom:20px;">
    Ringkasan hak akses tiap role dalam sistem. Halaman ini bersifat informatif — untuk mengubah akses, sesuaikan langsung di konfigurasi routing sistem.
</p>

<?php foreach ($roles as $role): ?>
<div class="card" style="margin-bottom:16px;">
    <h5 style="margin-bottom:4px;"><?= esc($role['nama']) ?></h5>
    <p style="color:#7c8a9c;font-size:13px;margin-bottom:14px;"><?= esc($role['desc']) ?></p>

    <?php foreach ($role['grup'] as $namaGrup => $modul): ?>
        <strong style="display:block;margin-bottom:6px;"><?= esc($namaGrup) ?></strong>
        <ul style="margin:0 0 14px 20px;">
            <?php foreach ($modul as $m): ?>
                <li style="font-size:13px;margin-bottom:4px;"><?= esc($m) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?= $this->endSection() ?>