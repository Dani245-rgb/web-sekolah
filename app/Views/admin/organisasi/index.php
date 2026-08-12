<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Organisasi Sekolah</h4>
    <a href="<?= base_url('admin/organisasi/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Organisasi
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<table class="table-admin">
    <thead>
        <tr>
            <th>Nama Organisasi</th>
            <th>Deskripsi</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($organisasi as $o): ?>
        <tr>
            <td><?= esc($o['nama']) ?></td>
            <td><?= esc($o['deskripsi'] ? character_limiter($o['deskripsi'], 60) : '-') ?></td>
            <td>
                <span class="badge <?= $o['status'] === 'Published' ? 'bg-success' : 'bg-warning' ?>">
                    <?= esc($o['status']) ?>
                </span>
            </td>
            <td class="d-flex gap-2">
                <a href="<?= base_url('admin/organisasi/' . $o['id'] . '/anggota') ?>" class="btn btn-sm btn-outline-primary">Kelola Anggota</a>
                <a href="<?= base_url('admin/organisasi/edit/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <form action="<?= base_url('admin/organisasi/delete/' . $o['id']) ?>" method="post" onsubmit="return confirm('Hapus organisasi ini beserta semua anggotanya?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($organisasi)): ?>
        <tr><td colspan="4" class="text-muted">Belum ada organisasi.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>