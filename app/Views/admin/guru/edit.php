<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Edit Guru</h4>
    <p class="breadcrumb">Dashboard / Data Guru / Edit</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/guru/update/' . $guru['id_guru']) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <fieldset>
            <legend>Akun Login</legend>
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?= esc($guru['username']) ?>" disabled>
                <small>Username tidak bisa diubah di sini.</small>
            </div>
        </fieldset>

        <fieldset>
            <legend>Data Guru</legend>

            <div class="form-group">
                <label>NIP</label>
                <input type="text" name="nip" value="<?= old('nip', $guru['nip']) ?>" required>
            </div>

            <div class="form-group">
                <label>NUPTK</label>
                <input type="text" name="nuptk" value="<?= old('nuptk', $guru['nuptk']) ?>">
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= old('nama', $guru['nama']) ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= old('tempat_lahir', $guru['tempat_lahir']) ?>">
                </div>
                <div class="form-group">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir', $guru['tanggal_lahir']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" required>
                    <option value="L" <?= $guru['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= $guru['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Jabatan / Mengajar Mapel</label>
                <input type="text" name="jabatan" value="<?= old('jabatan', $guru['jabatan']) ?>" placeholder="mis. Guru Matematika">
            </div>

            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="no_hp" value="<?= old('no_hp', $guru['no_hp']) ?>">
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= old('email', $guru['email']) ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="Aktif" <?= $guru['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="Nonaktif" <?= $guru['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>

            <div class="form-group">
                <label>Foto Saat Ini</label><br>
                <img src="<?= $guru['foto'] ? base_url('uploads/guru/' . $guru['foto']) : base_url('assets/images/default-avatar.png') ?>"
                    class="avatar-sm" style="width:60px;height:60px;"><br><br>
                <label>Ganti Foto (opsional)</label>
                <input type="file" name="foto" accept="image/*">
            </div>
        </fieldset>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?= base_url('admin/guru') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>