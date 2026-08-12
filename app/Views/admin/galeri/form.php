<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $galeri ? 'Edit Foto' : 'Tambah Foto' ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $galeri ? base_url('admin/galeri/update/' . $galeri['id']) : base_url('admin/galeri/store') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Judul Foto</label>
        <input type="text" name="judul" class="form-control" value="<?= old('judul', $galeri['judul'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Kategori</label>
        <select name="kategori" class="form-control">
            <?php $kategoriList = ['Kegiatan', 'Fasilitas', 'Prestasi', 'Lainnya']; ?>
            <?php foreach ($kategoriList as $k): ?>
                <option value="<?= $k ?>" <?= old('kategori', $galeri['kategori'] ?? '') === $k ? 'selected' : '' ?>>
                    <?= $k ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Deskripsi (opsional)</label>
        <textarea name="deskripsi" class="form-control" rows="3"><?= old('deskripsi', $galeri['deskripsi'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Published" <?= ($galeri['status'] ?? 'Published') === 'Published' ? 'selected' : '' ?>>Published</option>
            <option value="Draft" <?= ($galeri['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Foto <?= $galeri ? '(kosongkan jika tidak ganti)' : '' ?></label>
        <input type="file" name="foto" class="form-control" <?= $galeri ? '' : 'required' ?>>
        <?php if ($galeri): ?>
            <img src="<?= base_url('uploads/galeri/' . $galeri['foto']) ?>" width="120" class="mt-2 rounded">
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/galeri') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>