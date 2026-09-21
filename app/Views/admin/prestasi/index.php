<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Prestasi Sekolah</h4>
    <a href="<?= base_url('admin/prestasi/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Prestasi
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="row">
    <?php foreach ($prestasi as $p): ?>
        <div class="col-6 col-md-3 mb-4">
            <div class="card">
                <img src="<?= base_url('uploads/prestasi/' . $p['foto']) ?>" class="card-img-top" style="width:100%;height:160px;object-fit:cover;display:block;">
                <div class="card-body">
                    <h6 class="card-title"><?= esc($p['judul']) ?></h6>
                    <span class="badge bg-secondary"><?= esc($p['tingkat']) ?></span>
                    <span class="badge <?= $p['status'] === 'Published' ? 'bg-success' : 'bg-warning' ?>">
                        <?= esc($p['status']) ?>
                    </span>
                    <p class="text-muted mt-2" style="margin:6px 0 0;"><?= esc($p['tim']) ?> &middot; <?= date('d F Y', strtotime($p['tanggal'])) ?></p>
                    <div class="mt-2 d-flex gap-2">
                        <a href="<?= base_url('admin/prestasi/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="<?= base_url('admin/prestasi/delete/' . $p['id']) ?>" method="post" onsubmit="return confirm('Hapus prestasi ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($prestasi)): ?>
        <p class="text-muted">Belum ada prestasi.</p>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>