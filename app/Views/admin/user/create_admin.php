<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<h4>Tambah Akun Admin</h4>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= base_url('admin/user/store-admin') ?>" method="post" style="max-width:400px;">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" value="<?= old('username') ?>" required>
    </div>

    <div class="mb-3">
        <label>Password Awal</label>
        <input type="text" name="password" class="form-control" required>
        <small class="text-muted">Admin baru akan diminta ganti password saat login pertama.</small>
    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= base_url('admin/user') ?>" class="btn btn-secondary">Batal</a>
</form>

<?= $this->endSection() ?>