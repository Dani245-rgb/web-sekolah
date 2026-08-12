<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Pesan Masuk</h4>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<table class="table-admin">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Subjek</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pesan as $p): ?>
        <tr>
            <td><?= esc($p['nama']) ?></td>
            <td><?= esc($p['email']) ?></td>
            <td><?= esc($p['subjek'] ?: '-') ?></td>
            <td><?= date('d F Y H:i', strtotime($p['created_at'])) ?></td>
            <td>
                <span class="badge <?= $p['status'] === 'Sudah Dibaca' ? 'bg-success' : 'bg-warning' ?>">
                    <?= esc($p['status']) ?>
                </span>
            </td>
            <td class="d-flex gap-2">
                <a href="<?= base_url('admin/kontak/detail/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">Baca</a>
                <form action="<?= base_url('admin/kontak/delete/' . $p['id']) ?>" method="post" onsubmit="return confirm('Hapus pesan ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($pesan)): ?>
        <tr><td colspan="6" class="text-muted">Belum ada pesan masuk.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>