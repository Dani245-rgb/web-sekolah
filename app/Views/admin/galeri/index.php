<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Galeri Foto</h4>
    <a href="<?= base_url('admin/galeri/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Foto
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="row">
    <?php foreach ($galeri as $g): ?>
        <div class="col-md-3 mb-4">
            <div class="card">
                <img src="<?= base_url('uploads/galeri/' . $g['foto']) ?>" class="card-img-top" style="height:180px;object-fit:cover;">
                <div class="card-body">
                    <h6 class="card-title"><?= esc($g['judul']) ?></h6>
                    <span class="badge bg-secondary"><?= esc($g['kategori']) ?></span>
                    <span class="badge <?= $g['status'] === 'Published' ? 'bg-success' : 'bg-warning' ?>">
                        <?= esc($g['status']) ?>
                    </span>
                    <div class="mt-2 d-flex gap-2">
                        <a href="<?= base_url('admin/galeri/edit/' . $g['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="<?= base_url('admin/galeri/delete/' . $g['id']) ?>" method="post" onsubmit="return confirm('Hapus foto ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($galeri)): ?>
        <p class="text-muted">Belum ada foto di galeri.</p>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>