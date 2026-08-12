<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Pengumuman</h4>
    <a href="<?= base_url('admin/pengumuman/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Pengumuman
    </a>
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
            <th>Judul</th>
            <th>Tanggal Publish</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pengumuman as $p): ?>
        <tr>
            <td><?= esc($p['judul']) ?></td>
            <td><?= date('d F Y', strtotime($p['tanggal_publish'])) ?></td>
            <td>
                <span class="badge <?= $p['status'] === 'Published' ? 'bg-success' : 'bg-warning' ?>">
                    <?= esc($p['status']) ?>
                </span>
            </td>
            <td class="d-flex gap-2">
                <a href="<?= base_url('admin/pengumuman/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <form action="<?= base_url('admin/pengumuman/delete/' . $p['id']) ?>" method="post" onsubmit="return confirm('Hapus pengumuman ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($pengumuman)): ?>
        <tr>
            <td colspan="4" class="text-muted">Belum ada pengumuman.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>