<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Tambah Siswa</h4>
    <p class="breadcrumb">Dashboard / Data Siswa / Tambah</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/siswa/store') ?>" method="post" enctype="multipart/form-data"
        onsubmit="document.getElementById('btnSimpan').disabled=true; document.getElementById('btnSimpan').innerText='Menyimpan...';">
        <?= csrf_field() ?>

        <div class="form-row">
            <div class="form-group">
                <label>NIS</label>
                <input type="text" name="nis" value="<?= old('nis') ?>" required>
            </div>
            <div class="form-group">
                <label>NISN</label>
                <input type="text" name="nisn" value="<?= old('nisn') ?>" required>
            </div>
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
                <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir') ?>" required>
            </div>
            <div class="form-group">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih --</option>
                    <option value="L" <?= old('jenis_kelamin') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= old('jenis_kelamin') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
        </div>
        <small style="display:block;margin-bottom:14px;color:#7C8A9C;">Password awal login siswa otomatis dibuat dari
            tanggal lahir (format ddmmyyyy).</small>

        <div class="form-row">
            <div class="form-group">
                <label>Agama</label>
                <input type="text" name="agama" value="<?= old('agama') ?>">
            </div>
            <div class="form-group">
                <label>Email (opsional)</label>
                <input type="email" name="email" value="<?= old('email') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="2"><?= old('alamat') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Nama Ayah</label>
                <input type="text" name="nama_ayah" value="<?= old('nama_ayah') ?>">
            </div>
            <div class="form-group">
                <label>Nama Ibu</label>
                <input type="text" name="nama_ibu" value="<?= old('nama_ibu') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Pekerjaan Orang Tua</label>
                <input type="text" name="pekerjaan_ortu" value="<?= old('pekerjaan_ortu') ?>">
            </div>
            <div class="form-group">
                <label>No HP Orang Tua</label>
                <input type="text" name="no_hp_ortu" value="<?= old('no_hp_ortu') ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Foto</label>
            <input type="file" name="foto" accept="image/*">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
            <a href="<?= base_url('admin/siswa') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>