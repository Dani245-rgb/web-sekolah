<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4>Kepala Sekolah</h4>

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

<form action="<?= base_url('admin/profil-sekolah/update-kepala-sekolah') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="nama" class="form-control" value="<?= old('nama', $item['nama'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
        <label>Jabatan</label>
        <input type="text" name="jabatan" class="form-control" value="<?= old('jabatan', $item['jabatan'] ?? 'Kepala Sekolah') ?>" required>
    </div>

    <div class="mb-3">
        <label>Sambutan / Profil</label>
        <textarea name="konten" class="form-control" rows="8"><?= old('konten', $item['konten'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label>Foto</label>
        <input type="file" name="foto" class="form-control">
        <?php if (!empty($item['foto'])): ?>
            <img src="<?= base_url('uploads/profil/' . $item['foto']) ?>" width="120" class="mt-2 rounded">
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
</form>

<?= $this->endSection() ?>