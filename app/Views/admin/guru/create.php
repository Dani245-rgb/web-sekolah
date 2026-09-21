<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <h4>Tambah Guru</h4>
    <p class="breadcrumb">Dashboard / Data Guru / Tambah</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/guru/store') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <fieldset>
            <legend>Data Guru</legend>

            <div class="form-group">
                <label>NIP</label>
                <input type="text" name="nip" value="<?= old('nip') ?>" required>
            </div>

            <div class="form-group">
                <label>NUPTK</label>
                <input type="text" name="nuptk" value="<?= old('nuptk') ?>">
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= old('nama') ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= old('tempat_lahir') ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="L" <?= old('jenis_kelamin') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= old('jenis_kelamin') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Jabatan / Mengajar Mapel</label>
                <input type="text" name="jabatan" value="<?= old('jabatan') ?>" placeholder="mis. Guru Matematika">
            </div>

            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="no_hp" value="<?= old('no_hp') ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= old('email') ?>">
            </div>

            <div class="form-group">
                <label>Foto</label>
                <input type="file" name="foto" accept="image/*">
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?= base_url('admin/guru') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>