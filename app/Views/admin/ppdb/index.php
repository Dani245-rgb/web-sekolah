<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Pendaftar PPDB</h4>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="table-responsive-wrap">
<table class="table-admin">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Jurusan Pilihan</th>
            <th>No HP</th>
            <th>Tanggal Daftar</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pendaftar as $p): ?>
        <tr>
            <td><?= esc($p['nama_lengkap']) ?></td>
            <td><?= esc($p['jurusan_pilihan']) ?></td>
            <td><?= esc($p['no_hp']) ?></td>
            <td><?= date('d F Y', strtotime($p['created_at'])) ?></td>
            <td>
                <span class="badge <?= $p['status'] === 'Diterima' ? 'bg-success' : ($p['status'] === 'Ditolak' ? 'bg-danger' : 'bg-warning') ?>">
                    <?= esc($p['status']) ?>
                </span>
            </td>
            <td class="d-flex gap-2">
                <a href="<?= base_url('admin/ppdb/detail/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                <form action="<?= base_url('admin/ppdb/delete/' . $p['id']) ?>" method="post" onsubmit="return confirm('Hapus data pendaftar ini?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($pendaftar)): ?>
        <tr><td colspan="6" class="text-muted">Belum ada pendaftar.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?= $this->endSection() ?>
