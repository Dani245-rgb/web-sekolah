<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $pengumuman ? 'Edit Pengumuman' : 'Tambah Pengumuman' ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $pengumuman ? base_url('admin/pengumuman/update/' . $pengumuman['id']) : base_url('admin/pengumuman/store') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="judul" class="form-control" value="<?= old('judul', $pengumuman['judul'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Isi Pengumuman</label>
        <textarea name="isi" class="form-control" rows="6" required><?= old('isi', $pengumuman['isi'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label>Tanggal Publish</label>
        <input type="date" name="tanggal_publish" class="form-control"
               value="<?= old('tanggal_publish', $pengumuman['tanggal_publish'] ?? date('Y-m-d')) ?>" required>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Published" <?= ($pengumuman['status'] ?? '') === 'Published' ? 'selected' : '' ?>>Published</option>
            <option value="Draft" <?= ($pengumuman['status'] ?? 'Draft') === 'Draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/pengumuman') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>