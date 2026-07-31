<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Edit Mata Pelajaran</h4>
    <p class="breadcrumb">Dashboard / Mata Pelajaran / Edit</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/mapel/update/' . $mapelData['id_mapel']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label>Kode Mapel</label>
                <input type="text" name="kode_mapel" value="<?= old('kode_mapel', $mapelData['kode_mapel']) ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Nama Mapel</label>
                <input type="text" name="nama_mapel" value="<?= old('nama_mapel', $mapelData['nama_mapel']) ?>"
                    required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Kelompok Mapel</label>
                <select name="kelompok_mapel">
                    <option value="">-- Pilih --</option>
                    <option value="Wajib" <?= $mapelData['kelompok_mapel'] === 'Wajib' ? 'selected' : '' ?>>Wajib
                    </option>
                    <option value="Peminatan" <?= $mapelData['kelompok_mapel'] === 'Peminatan' ? 'selected' : '' ?>>
                        Peminatan</option>
                    <option value="Muatan Lokal"
                        <?= $mapelData['kelompok_mapel'] === 'Muatan Lokal' ? 'selected' : '' ?>>Muatan Lokal</option>
                </select>
            </div>

            <div class="form-group">
                <label>KKM</label>
                <input type="number" name="kkm" min="0" max="100" value="<?= old('kkm', $mapelData['kkm']) ?>">
            </div>

            <div class="form-group">
                <label>Semester</label>
                <select name="semester">
                    <option value="">-- Pilih --</option>
                    <option value="Ganjil" <?= $mapelData['semester'] === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                    <option value="Genap" <?= $mapelData['semester'] === 'Genap' ? 'selected' : '' ?>>Genap</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="Aktif" <?= $mapelData['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="Nonaktif" <?= $mapelData['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?= base_url('admin/mapel') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>