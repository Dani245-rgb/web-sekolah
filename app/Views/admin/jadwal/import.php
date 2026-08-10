<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Import Jadwal dari Excel</h4>
    <p class="breadcrumb">Dashboard / Jadwal Pelajaran / Import</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <p style="margin-bottom:16px;">
        1. Download template, isi sesuai contoh (cek sheet "Referensi" untuk kode mapel, NIP guru, dan nama ruangan yang
        valid).<br>
        2. Upload file yang sudah diisi — hasilnya akan ditampilkan dulu di halaman Preview sebelum masuk database.
    </p>

    <a href="<?= base_url('admin/jadwal/template') ?>" class="btn btn-secondary" style="margin-bottom:20px;">⬇️ Download
        Template</a>

    <form action="<?= base_url('admin/jadwal/import/preview') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>File Excel (.xlsx)</label>
            <input type="file" name="file_jadwal" accept=".xlsx" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload & Lihat Preview</button>
        <a href="<?= base_url('admin/jadwal') ?>" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?= $this->endSection() ?>