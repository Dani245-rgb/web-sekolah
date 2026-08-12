<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $ekskul ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler' ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $ekskul ? base_url('admin/ekstrakurikuler/update/' . $ekskul['id']) : base_url('admin/ekstrakurikuler/store') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Nama Ekstrakurikuler</label>
        <input type="text" name="nama" class="form-control" value="<?= old('nama', $ekskul['nama'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Deskripsi (opsional)</label>
        <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi', $ekskul['deskripsi'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Published" <?= ($ekskul['status'] ?? 'Published') === 'Published' ? 'selected' : '' ?>>Published</option>
            <option value="Draft" <?= ($ekskul['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Foto <?= $ekskul ? '(kosongkan jika tidak ganti)' : '(wajib)' ?></label>
        <input type="file" name="foto" class="form-control" <?= $ekskul ? '' : 'required' ?>>
        <?php if ($ekskul): ?>
            <img src="<?= base_url('uploads/ekstrakurikuler/' . $ekskul['foto']) ?>" width="120" class="mt-2 rounded">
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/ekstrakurikuler') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>