<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Agenda Sekolah</h4>
    <a href="<?= base_url('admin/agenda/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Agenda
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<table class="table-admin">
    <thead>
        <tr>
            <th>Judul</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Lokasi</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($agenda as $a): ?>
        <tr>
            <td><?= esc($a['judul']) ?></td>
            <td><?= date('d F Y', strtotime($a['tanggal'])) ?></td>
            <td><?= esc($a['waktu']) ?></td>
            <td><?= esc($a['lokasi']) ?></td>
            <td>
                <span class="badge <?= $a['status'] === 'Published' ? 'bg-success' : 'bg-warning' ?>">
                    <?= esc($a['status']) ?>
                </span>
            </td>
            <td class="d-flex gap-2">
                <a href="<?= base_url('admin/agenda/edit/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                <form action="<?= base_url('admin/agenda/delete/' . $a['id']) ?>" method="post" onsubmit="return confirm('Hapus agenda ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($agenda)): ?>
        <tr><td colspan="6" class="text-muted">Belum ada agenda.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>