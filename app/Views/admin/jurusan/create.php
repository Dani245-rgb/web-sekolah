<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/jurusan.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="jurusan-page-header">
    <h4>Tambah Jurusan</h4>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<form action="<?= base_url('admin/jurusan/store') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="nama_jurusan">Nama Jurusan <span style="color:red;">*</span></label>
        <input type="text" name="nama_jurusan" id="nama_jurusan" class="form-control"
               value="<?= esc(old('nama_jurusan') ?? '') ?>" maxlength="100" required>
        <?php if (isset($errors['nama_jurusan'])): ?>
        <div class="text-danger"><?= esc($errors['nama_jurusan']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="deskripsi">Deskripsi</label>
        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4"
                  maxlength="5000"><?= esc(old('deskripsi') ?? '') ?></textarea>
        <?php if (isset($errors['deskripsi'])): ?>
        <div class="text-danger"><?= esc($errors['deskripsi']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="kompetensi">Kompetensi</label>
        <textarea name="kompetensi" id="kompetensi" class="form-control" rows="4"
                  maxlength="5000"><?= esc(old('kompetensi') ?? '') ?></textarea>
        <?php if (isset($errors['kompetensi'])): ?>
        <div class="text-danger"><?= esc($errors['kompetensi']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="prospek_kerja">Prospek Kerja</label>
        <textarea name="prospek_kerja" id="prospek_kerja" class="form-control" rows="4"
                  maxlength="5000"><?= esc(old('prospek_kerja') ?? '') ?></textarea>
        <?php if (isset($errors['prospek_kerja'])): ?>
        <div class="text-danger"><?= esc($errors['prospek_kerja']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="foto">Foto Jurusan (opsional, maks 2MB — jpg/jpeg/png/webp)</label>
        <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
        <?php if (isset($errors['foto'])): ?>
        <div class="text-danger"><?= esc($errors['foto']) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/jurusan') ?>" class="btn btn-outline-secondary">Batal</a>
</form>

<?= $this->endSection() ?>