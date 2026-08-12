<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $item ? 'Edit Kegiatan' : 'Tambah Kegiatan' ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $item ? base_url('admin/kalender-akademik/update/' . $item['id']) : base_url('admin/kalender-akademik/store') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Nama Kegiatan</label>
        <input type="text" name="kegiatan" class="form-control" value="<?= old('kegiatan', $item['kegiatan'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Tanggal Mulai</label>
        <input type="date" name="tanggal_mulai" class="form-control" value="<?= old('tanggal_mulai', $item['tanggal_mulai'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Tanggal Selesai (opsional)</label>
        <input type="date" name="tanggal_selesai" class="form-control" value="<?= old('tanggal_selesai', $item['tanggal_selesai'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label>Keterangan (opsional)</label>
        <textarea name="keterangan" class="form-control" rows="3"><?= old('keterangan', $item['keterangan'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label>Semester</label>
        <select name="semester" class="form-control">
            <option value="Ganjil" <?= ($item['semester'] ?? '') === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
            <option value="Genap" <?= ($item['semester'] ?? '') === 'Genap' ? 'selected' : '' ?>>Genap</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Tahun Ajaran</label>
        <input type="text" name="tahun_ajaran" class="form-control" value="<?= old('tahun_ajaran', $item['tahun_ajaran'] ?? '') ?>" placeholder="Contoh: 2026/2027" required>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Published" <?= ($item['status'] ?? 'Published') === 'Published' ? 'selected' : '' ?>>Published</option>
            <option value="Draft" <?= ($item['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/kalender-akademik') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>