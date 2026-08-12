<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $prestasi ? 'Edit Prestasi' : 'Tambah Prestasi' ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $prestasi ? base_url('admin/prestasi/update/' . $prestasi['id']) : base_url('admin/prestasi/store') ?>"
      method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Judul Prestasi</label>
        <input type="text" name="judul" class="form-control" value="<?= old('judul', $prestasi['judul'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Tim / Jurusan</label>
        <input type="text" name="tim" class="form-control" value="<?= old('tim', $prestasi['tim'] ?? '') ?>" placeholder="Contoh: Tim Tata Boga">
    </div>

    <div class="mb-3">
        <label>Tingkat</label>
        <select name="tingkat" class="form-control">
            <?php $tingkatList = ['Kabupaten', 'Provinsi', 'Nasional', 'Internasional']; ?>
            <?php foreach ($tingkatList as $t): ?>
                <option value="<?= $t ?>" <?= old('tingkat', $prestasi['tingkat'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-control" value="<?= old('tanggal', $prestasi['tanggal'] ?? date('Y-m-d')) ?>" required>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Published" <?= ($prestasi['status'] ?? 'Published') === 'Published' ? 'selected' : '' ?>>Published</option>
            <option value="Draft" <?= ($prestasi['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Foto <?= $prestasi ? '(kosongkan jika tidak ganti)' : '' ?></label>
        <input type="file" name="foto" class="form-control" <?= $prestasi ? '' : 'required' ?>>
        <?php if ($prestasi): ?>
            <img src="<?= base_url('uploads/prestasi/' . $prestasi['foto']) ?>" width="120" class="mt-2 rounded">
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/prestasi') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>