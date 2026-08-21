<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/bk-artikel.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="bk-artikel-page-header">
    <h4>Tambah Artikel BK</h4>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<form action="<?= base_url('admin/bk-artikel/store') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="kategori">Kategori <span style="color:red;">*</span></label>
        <select name="kategori" id="kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="kesehatan_mental" <?= old('kategori') === 'kesehatan_mental' ? 'selected' : '' ?>>Kesehatan Mental</option>
            <option value="karier" <?= old('kategori') === 'karier' ? 'selected' : '' ?>>Karier & Studi Lanjut</option>
            <option value="tes_minat" <?= old('kategori') === 'tes_minat' ? 'selected' : '' ?>>Tes Minat & Bakat</option>
        </select>
        <?php if (isset($errors['kategori'])): ?>
        <div class="text-danger"><?= esc($errors['kategori']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="judul">Judul <span style="color:red;">*</span></label>
        <input type="text" name="judul" id="judul" class="form-control"
               value="<?= esc(old('judul') ?? '') ?>" maxlength="255" required>
        <?php if (isset($errors['judul'])): ?>
        <div class="text-danger"><?= esc($errors['judul']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="konten">Konten</label>
        <textarea name="konten" id="konten" class="form-control" rows="6"
                  maxlength="20000"><?= esc(old('konten') ?? '') ?></textarea>
        <?php if (isset($errors['konten'])): ?>
        <div class="text-danger"><?= esc($errors['konten']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="link_eksternal">Link Eksternal (opsional — untuk Tes Minat/link pendaftaran Beasiswa)</label>
        <input type="url" name="link_eksternal" id="link_eksternal" class="form-control"
               value="<?= esc(old('link_eksternal') ?? '') ?>" maxlength="255" placeholder="https://...">
        <?php if (isset($errors['link_eksternal'])): ?>
        <div class="text-danger"><?= esc($errors['link_eksternal']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="deadline">Deadline (opsional — untuk Beasiswa/Pendaftaran)</label>
        <input type="date" name="deadline" id="deadline" class="form-control"
               value="<?= esc(old('deadline') ?? '') ?>">
        <?php if (isset($errors['deadline'])): ?>
        <div class="text-danger"><?= esc($errors['deadline']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="foto">Foto/Thumbnail (opsional, maks 2MB — jpg/jpeg/png/webp)</label>
        <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
        <?php if (isset($errors['foto'])): ?>
        <div class="text-danger"><?= esc($errors['foto']) ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status" class="form-control">
            <option value="Draft" <?= old('status') === 'Draft' ? 'selected' : '' ?>>Draft</option>
            <option value="Published" <?= old('status') === 'Published' ? 'selected' : '' ?>>Published</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/bk-artikel') ?>" class="btn btn-outline-secondary">Batal</a>
</form>

<?= $this->endSection() ?>