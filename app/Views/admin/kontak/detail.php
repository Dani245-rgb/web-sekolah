<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4>Detail Pesan</h4>

<table class="table-admin" style="max-width:600px;">
    <tbody>
        <tr><th style="width:150px;">Nama</th><td><?= esc($pesan['nama']) ?></td></tr>
        <tr><th>Email</th><td><?= esc($pesan['email']) ?></td></tr>
        <tr><th>Subjek</th><td><?= esc($pesan['subjek'] ?: '-') ?></td></tr>
        <tr><th>Tanggal</th><td><?= date('d F Y H:i', strtotime($pesan['created_at'])) ?></td></tr>
        <tr><th>Pesan</th><td><?= nl2br(esc($pesan['pesan'])) ?></td></tr>
    </tbody>
</table>

<div style="margin-top:20px;">
    <a href="mailto:<?= esc($pesan['email']) ?>" class="btn btn-primary">
        <i class="fa-solid fa-reply"></i> Balas via Email
    </a>
    <a href="<?= base_url('admin/kontak') ?>" class="btn btn-secondary">Kembali</a>
</div>

<?= $this->endSection() ?>