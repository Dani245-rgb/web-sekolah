<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Backup Database</h4>
    <form action="<?= base_url('admin/backup-database/create') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-primary" onclick="return confirm('Buat backup database sekarang?');">
            <i class="fa-solid fa-database"></i> Buat Backup Baru
        </button>
    </form>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<table class="table-admin">
    <thead>
        <tr>
            <th>Nama File</th>
            <th>Ukuran</th>
            <th>Tanggal Dibuat</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($backups as $b): ?>
        <tr>
            <td><?= esc($b['nama']) ?></td>
            <td><?= esc($b['ukuran']) ?></td>
            <td><?= esc($b['tanggal']) ?></td>
            <td class="d-flex gap-2" style="flex-wrap:wrap;">
                <a href="<?= base_url('admin/backup-database/download/' . $b['nama']) ?>" class="btn btn-sm btn-outline-primary">Download</a>
                <form action="<?= base_url('admin/backup-database/delete/' . $b['nama']) ?>" method="post" onsubmit="return confirm('Hapus file backup ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($backups)): ?>
        <tr><td colspan="4" class="text-muted">Belum ada backup.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>