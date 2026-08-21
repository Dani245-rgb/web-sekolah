<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/unduhan.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="unduhan-page-header">
    <h4>Edit File Unduhan</h4>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<form action="<?= base_url('admin/unduhan/update/' . $item['id_unduhan']) ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="kategori">Kategori <span style="color:red;">*</span></label>
        <input type="text" name="kategori" id="kategori" class="form-control" list="kategori-list"
               value="<?= esc(old('kategori') ?? $item['kategori']) ?>" maxlength="100" required>
        <datalist id="kategori-list">
            <option value="Logo Sekolah">
            <option value="Logo Jurusan">
            <option value="Logo Ekskul">
            <option value="Logo Organisasi">
            <?php foreach ($kategoriList as $k): ?>
            <option value="<?= esc($k['kategori'], 'attr') ?>">
            <?php endforeach; ?>
        </datalist>
        <?php if (isset($errors['kategori'])): ?>
        <div class="text-danger"><?= esc($errors['kategori']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="judul">Judul <span style="color:red;">*</span></label>
        <input type="text" name="judul" id="judul" class="form-control"
               value="<?= esc(old('judul') ?? $item['judul']) ?>" maxlength="255" required>
        <?php if (isset($errors['judul'])): ?>
        <div class="text-danger"><?= esc($errors['judul']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="deskripsi">Deskripsi (opsional)</label>
        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"
                  maxlength="2000"><?= esc(old('deskripsi') ?? $item['deskripsi'] ?? '') ?></textarea>
        <?php if (isset($errors['deskripsi'])): ?>
        <div class="text-danger"><?= esc($errors['deskripsi']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>File Saat Ini</label><br>
        <p><?= esc($item['nama_file_asli'] ?? $item['file']) ?> (<?= esc(strtoupper($item['ekstensi'])) ?>)</p>
    </div>

    <div class="form-group">
        <label for="file">Ganti File (opsional, maks 10MB)</label>
        <input type="file" name="file" id="file" class="form-control">
        <?php if (isset($errors['file'])): ?>
        <div class="text-danger"><?= esc($errors['file']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status" class="form-control">
            <option value="Draft" <?= (old('status') ?? $item['status']) === 'Draft' ? 'selected' : '' ?>>Draft</option>
            <option value="Published" <?= (old('status') ?? $item['status']) === 'Published' ? 'selected' : '' ?>>Published</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="<?= base_url('admin/unduhan') ?>" class="btn btn-outline-secondary">Batal</a>
</form>

<?= $this->endSection() ?>