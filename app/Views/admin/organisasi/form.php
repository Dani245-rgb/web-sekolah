<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $item ? 'Edit Organisasi' : 'Tambah Organisasi' ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $item ? base_url('admin/organisasi/update/' . $item['id']) : base_url('admin/organisasi/store') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Nama Organisasi</label>
        <input type="text" name="nama" class="form-control" value="<?= old('nama', $item['nama'] ?? '') ?>" placeholder="Contoh: OSIS" required>
    </div>

    <div class="mb-3">
        <label>Deskripsi (opsional)</label>
        <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi', $item['deskripsi'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Published" <?= ($item['status'] ?? 'Published') === 'Published' ? 'selected' : '' ?>>Published</option>
            <option value="Draft" <?= ($item['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/organisasi') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>