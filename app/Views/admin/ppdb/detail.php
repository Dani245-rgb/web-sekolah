<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4>Detail Pendaftar</h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<table class="table-admin" style="max-width:600px;">
    <tbody>
        <tr><th style="width:180px;">Nama Lengkap</th><td><?= esc($pendaftar['nama_lengkap']) ?></td></tr>
        <tr><th>Tempat, Tanggal Lahir</th><td><?= esc($pendaftar['tempat_lahir']) ?>, <?= $pendaftar['tanggal_lahir'] ? date('d F Y', strtotime($pendaftar['tanggal_lahir'])) : '-' ?></td></tr>
        <tr><th>Jenis Kelamin</th><td><?= esc($pendaftar['jenis_kelamin']) ?></td></tr>
        <tr><th>Asal Sekolah</th><td><?= esc($pendaftar['asal_sekolah']) ?></td></tr>
        <tr><th>Jurusan Pilihan</th><td><?= esc($pendaftar['jurusan_pilihan']) ?></td></tr>
        <tr><th>No HP</th><td><?= esc($pendaftar['no_hp']) ?></td></tr>
        <tr><th>Email</th><td><?= esc($pendaftar['email']) ?></td></tr>
        <tr><th>Alamat</th><td><?= nl2br(esc($pendaftar['alamat'])) ?></td></tr>
        <tr><th>Tanggal Daftar</th><td><?= date('d F Y H:i', strtotime($pendaftar['created_at'])) ?></td></tr>
    </tbody>
</table>

<form action="<?= base_url('admin/ppdb/update-status/' . $pendaftar['id']) ?>" method="post" style="margin-top:20px;max-width:300px;">
    <?= csrf_field() ?>
    <div class="mb-3">
        <label>Ubah Status</label>
        <select name="status" class="form-control">
            <option value="Menunggu" <?= $pendaftar['status'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
            <option value="Diterima" <?= $pendaftar['status'] === 'Diterima' ? 'selected' : '' ?>>Diterima</option>
            <option value="Ditolak" <?= $pendaftar['status'] === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Status</button>
    <a href="<?= base_url('admin/ppdb') ?>" class="btn btn-secondary">Kembali</a>
</form>

<?= $this->endSection() ?>