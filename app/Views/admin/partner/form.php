<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $partner ? 'Edit Partner' : 'Tambah Partner' ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $partner ? base_url('admin/partner/update/' . $partner['id']) : base_url('admin/partner/store') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Nama Partner</label>
        <input type="text" name="nama" class="form-control" value="<?= old('nama', $partner['nama'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="deskripsi" class="form-control" rows="4" required><?= old('deskripsi', $partner['deskripsi'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Published" <?= ($partner['status'] ?? 'Published') === 'Published' ? 'selected' : '' ?>>Published</option>
            <option value="Draft" <?= ($partner['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Foto <?= $partner ? '(kosongkan jika tidak ganti)' : '(wajib)' ?></label>
        <input type="file" name="foto" class="form-control" <?= $partner ? '' : 'required' ?>>
        <?php if ($partner): ?>
            <img src="<?= base_url('uploads/partner/' . $partner['foto']) ?>" width="120" class="mt-2 rounded">
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/partner') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>