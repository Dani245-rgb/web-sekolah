<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <h4>Pengaturan PPDB</h4>
    <p class="breadcrumb">Dashboard / PPDB / Pengaturan</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <form method="post" action="<?= base_url('admin/ppdb/pengaturan/update') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Status Pendaftaran</label>
            <select name="status" required>
                <option value="buka" <?= $setting['status'] === 'buka' ? 'selected' : '' ?>>Buka</option>
                <option value="tutup" <?= $setting['status'] === 'tutup' ? 'selected' : '' ?>>Tutup</option>
            </select>
        </div>

        <div class="form-group">
            <label>Tahun Ajaran</label>
            <input type="text" name="tahun_ajaran"
                   value="<?= esc($setting['tahun_ajaran'] ?? '') ?>" placeholder="2026/2027">
        </div>

        <div class="form-group">
            <label>Pesan saat Ditutup</label>
            <textarea name="pesan_tutup" rows="3" style="width:100%;padding:9px 12px;border:1px solid #d7dce3;border-radius:6px;font-size:13px;font-family:inherit;resize:vertical;"><?= esc($setting['pesan_tutup'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>