<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Tambah Mata Pelajaran</h4>
    <p class="breadcrumb">Dashboard / Mata Pelajaran / Tambah</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/mapel/store') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label>Kode Mapel</label>
                <input type="text" name="kode_mapel" placeholder="MTK-01" value="<?= old('kode_mapel') ?>" required>
            </div>

            <div class="form-group">
                <label>Nama Mapel</label>
                <input type="text" name="nama_mapel" placeholder="Matematika" value="<?= old('nama_mapel') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Kelompok Mapel</label>
                <select name="kelompok_mapel">
                    <option value="">-- Pilih --</option>
                    <option value="Wajib" <?= old('kelompok_mapel') === 'Wajib' ? 'selected' : '' ?>>Wajib</option>
                    <option value="Peminatan" <?= old('kelompok_mapel') === 'Peminatan' ? 'selected' : '' ?>>Peminatan
                    </option>
                    <option value="Muatan Lokal" <?= old('kelompok_mapel') === 'Muatan Lokal' ? 'selected' : '' ?>>
                        Muatan Lokal</option>
                </select>
            </div>

            <div class="form-group">
                <label>KKM</label>
                <input type="number" name="kkm" min="0" max="100" value="<?= old('kkm', 75) ?>">
            </div>

            <div class="form-group">
                <label>Semester</label>
                <select name="semester">
                    <option value="">-- Pilih --</option>
                    <option value="Ganjil" <?= old('semester') === 'Ganjil' ? 'selected' : '' ?>>Ganjil</option>
                    <option value="Genap" <?= old('semester') === 'Genap' ? 'selected' : '' ?>>Genap</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('admin/mapel') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>