<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Edit Tahun Ajaran</h4>
    <p class="breadcrumb">Dashboard / Tahun Ajaran / Edit</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/tahun-ajaran/update/' . $tahunAjaran['id_tahun_ajaran']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Tahun Ajaran</label>
            <input type="text" name="tahun_ajaran" value="<?= old('tahun_ajaran', $tahunAjaran['tahun_ajaran']) ?>"
                required>
        </div>

        <div class="form-group">
            <label>Semester</label>
            <select name="semester">
                <option value="Ganjil" <?= $tahunAjaran['semester'] === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                <option value="Genap" <?= $tahunAjaran['semester'] === 'Genap' ? 'selected' : '' ?>>Genap</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai"
                    value="<?= old('tanggal_mulai', $tahunAjaran['tanggal_mulai']) ?>">
            </div>
            <div class="form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai"
                    value="<?= old('tanggal_selesai', $tahunAjaran['tanggal_selesai']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" required>
                <option value="Belum Aktif" <?= $tahunAjaran['status'] === 'Belum Aktif' ? 'selected' : '' ?>>Belum
                    Aktif</option>
                <option value="Aktif" <?= $tahunAjaran['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="Tidak Aktif" <?= $tahunAjaran['status'] === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak
                    Aktif</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?= base_url('admin/tahun-ajaran') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>