<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4><?= $agenda ? 'Edit Agenda' : 'Tambah Agenda' ?></h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= $agenda ? base_url('admin/agenda/update/' . $agenda['id']) : base_url('admin/agenda/store') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Judul Agenda</label>
        <input type="text" name="judul" class="form-control" value="<?= old('judul', $agenda['judul'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Tanggal</label>
        <input type="date" name="tanggal" class="form-control" value="<?= old('tanggal', $agenda['tanggal'] ?? date('Y-m-d')) ?>" required>
    </div>

    <div class="mb-3">
        <label>Waktu</label>
        <input type="text" name="waktu" class="form-control" value="<?= old('waktu', $agenda['waktu'] ?? '') ?>" placeholder="Contoh: 08.00 - Selesai">
    </div>

    <div class="mb-3">
        <label>Lokasi</label>
        <input type="text" name="lokasi" class="form-control" value="<?= old('lokasi', $agenda['lokasi'] ?? '') ?>" placeholder="Contoh: Aula Sekolah">
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="Published" <?= ($agenda['status'] ?? 'Published') === 'Published' ? 'selected' : '' ?>>Published</option>
            <option value="Draft" <?= ($agenda['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/agenda') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>