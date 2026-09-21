<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<h4 style="margin-bottom:16px;">Pengaturan Sekolah</h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card" style="max-width:500px;">
    <form action="<?= base_url('admin/pengaturan/update') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nama Sekolah</label>
            <input type="text" name="nama_sekolah" value="<?= esc($pengaturan['nama_sekolah'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="3" style="width:100%;padding:9px 12px;border:1px solid #d7dce3;border-radius:6px;font-size:13px;font-family:inherit;"><?= esc($pengaturan['alamat'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Telepon</label>
            <input type="text" name="telepon" value="<?= esc($pengaturan['telepon'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= esc($pengaturan['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Logo</label>
            <?php if (!empty($pengaturan['logo'])): ?>
                <img src="<?= base_url('assets/uploads/sekolah/' . $pengaturan['logo']) ?>" alt="Logo" style="height:60px;margin-bottom:8px;display:block;">
            <?php endif; ?>
            <input type="file" name="logo" accept="image/*">
        </div>

        <div class="form-group">
            <label>Favicon</label>
            <?php if (!empty($pengaturan['favicon'])): ?>
                <img src="<?= base_url('assets/uploads/sekolah/' . $pengaturan['favicon']) ?>" alt="Favicon" style="height:32px;margin-bottom:8px;display:block;">
            <?php endif; ?>
            <input type="file" name="favicon" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </form>
</div>

<?= $this->endSection() ?>