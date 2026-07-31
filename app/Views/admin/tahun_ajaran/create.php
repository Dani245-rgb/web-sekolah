<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Tambah Tahun Ajaran</h4>
    <p class="breadcrumb">Dashboard / Tahun Ajaran / Tambah</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/tahun-ajaran/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Tahun Ajaran</label>
            <input type="text" name="tahun_ajaran" placeholder="2026/2027" value="<?= old('tahun_ajaran') ?>" required>
        </div>

        <div class="form-group">
            <label>Semester</label>
            <select name="semester">
                <option value="">-- Pilih --</option>
                <option value="Ganjil" <?= old('semester') === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                <option value="Genap" <?= old('semester') === 'Genap' ? 'selected' : '' ?>>Genap</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="<?= old('tanggal_mulai') ?>">
            </div>
            <div class="form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai" value="<?= old('tanggal_selesai') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" required>
                <option value="Belum Aktif" <?= old('status') === 'Belum Aktif' ? 'selected' : '' ?>>Belum Aktif
                </option>
                <option value="Aktif" <?= old('status') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="Tidak Aktif" <?= old('status') === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif
                </option>
            </select>
            <small>Kalau dipilih "Aktif", tahun ajaran lain otomatis jadi "Tidak Aktif".</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('admin/tahun-ajaran') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>