<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Kalender Akademik</h4>
    <a href="<?= base_url('admin/kalender-akademik/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Kegiatan
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<table class="table-admin">
    <thead>
        <tr>
            <th>Kegiatan</th>
            <th>Tanggal</th>
            <th>Semester</th>
            <th>Tahun Ajaran</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($kalender as $k): ?>
        <tr>
            <td><?= esc($k['kegiatan']) ?></td>
            <td>
                <?= date('d M Y', strtotime($k['tanggal_mulai'])) ?>
                <?= $k['tanggal_selesai'] ? ' - ' . date('d M Y', strtotime($k['tanggal_selesai'])) : '' ?>
            </td>
            <td><?= esc($k['semester']) ?></td>
            <td><?= esc($k['tahun_ajaran']) ?></td>
            <td>
                <span class="badge <?= $k['status'] === 'Published' ? 'bg-success' : 'bg-warning' ?>">
                    <?= esc($k['status']) ?>
                </span>
            </td>
            <td class="d-flex gap-2">
                <a href="<?= base_url('admin/kalender-akademik/edit/' . $k['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <form action="<?= base_url('admin/kalender-akademik/delete/' . $k['id']) ?>" method="post" onsubmit="return confirm('Hapus kegiatan ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($kalender)): ?>
        <tr><td colspan="6" class="text-muted">Belum ada kegiatan.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>