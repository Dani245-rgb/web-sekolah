<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Ekstrakurikuler</h4>
    <a href="<?= base_url('admin/ekstrakurikuler/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Ekstrakurikuler
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="row">
    <?php foreach ($ekskul as $e): ?>
        <div class="col-md-3 mb-4">
            <div class="card">
                <img src="<?= base_url('uploads/ekstrakurikuler/' . $e['foto']) ?>" class="card-img-top" style="height:160px;object-fit:cover;">
                <div class="card-body">
                    <h6 class="card-title"><?= esc($e['nama']) ?></h6>
                    <span class="badge <?= $e['status'] === 'Published' ? 'bg-success' : 'bg-warning' ?>">
                        <?= esc($e['status']) ?>
                    </span>
                    <div class="mt-2 d-flex gap-2">
                        <a href="<?= base_url('admin/ekstrakurikuler/edit/' . $e['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="<?= base_url('admin/ekstrakurikuler/delete/' . $e['id']) ?>" method="post" onsubmit="return confirm('Hapus ekstrakurikuler ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($ekskul)): ?>
        <p class="text-muted">Belum ada data ekstrakurikuler.</p>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>