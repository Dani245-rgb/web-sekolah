<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Anggota — <?= esc($organisasi['nama']) ?></h4>
    <a href="<?= base_url('admin/organisasi/' . $organisasi['id'] . '/anggota/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Anggota
    </a>
</div>

<a href="<?= base_url('admin/organisasi') ?>" style="display:inline-block;margin-bottom:16px;">&larr; Kembali ke Daftar Organisasi</a>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="row">
    <?php foreach ($anggota as $a): ?>
        <div class="col-md-3 mb-4">
            <div class="card" style="text-align:center;padding:16px;">
                <img src="<?= $a['foto'] ? base_url('uploads/organisasi/' . $a['foto']) : base_url('assets/images/default-avatar.png') ?>"
                     style="width:100px;height:100px;object-fit:cover;border-radius:50%;margin:0 auto 10px;">
                <h6 class="card-title"><?= esc($a['nama']) ?></h6>
                <p class="text-muted" style="margin:0 0 10px;"><?= esc($a['jabatan']) ?></p>
                <div class="d-flex gap-2" style="justify-content:center;">
                    <a href="<?= base_url('admin/organisasi/' . $organisasi['id'] . '/anggota/edit/' . $a['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="<?= base_url('admin/organisasi/' . $organisasi['id'] . '/anggota/delete/' . $a['id']) ?>" method="post" onsubmit="return confirm('Hapus anggota ini?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($anggota)): ?>
        <p class="text-muted">Belum ada anggota.</p>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>