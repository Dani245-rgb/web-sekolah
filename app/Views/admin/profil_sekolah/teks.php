<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div style="display:flex;gap:10px;margin-bottom:20px;">
    <a href="<?= base_url('admin/profil-sekolah/sejarah') ?>"
       class="btn <?= $jenis === 'sejarah' ? 'btn-primary' : 'btn-secondary' ?>">Sejarah Sekolah</a>
    <a href="<?= base_url('admin/profil-sekolah/visi-misi') ?>"
       class="btn <?= $jenis === 'visi_misi' ? 'btn-primary' : 'btn-secondary' ?>">Visi & Misi</a>
    <a href="<?= base_url('admin/profil-sekolah/kepala-sekolah') ?>"
       class="btn btn-secondary">Kepala Sekolah</a>
</div>

<h4><?= esc($label) ?></h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= base_url('admin/profil-sekolah/update-teks/' . $jenis) ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="judul" class="form-control" value="<?= old('judul', $item['judul'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Konten</label>
        <textarea name="konten" class="form-control" rows="10"><?= old('konten', $item['konten'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
</form>

<?= $this->endSection() ?>