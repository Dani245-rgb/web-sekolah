<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Edit Siswa</h4>
    <p class="breadcrumb">Dashboard / Data Siswa / Edit</p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form action="<?= base_url('admin/siswa/update/' . $siswaData['id_siswa']) ?>" method="post"
        enctype="multipart/form-data"
        onsubmit="document.getElementById('btnUpdate').disabled=true; document.getElementById('btnUpdate').innerText='Menyimpan...';">
        <?= csrf_field() ?>

        <?php if (!empty($siswaData['foto'])): ?>
        <div class="form-group">
            <label>Foto Saat Ini</label><br>
            <img src="<?= base_url('uploads/siswa/' . $siswaData['foto']) ?>" width="100" style="border-radius:8px;">
        </div>
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>NIS</label>
                <input type="text" name="nis" value="<?= old('nis', $siswaData['nis']) ?>" required>
            </div>
            <div class="form-group">
                <label>NISN</label>
                <input type="text" name="nisn" value="<?= old('nisn', $siswaData['nisn']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" value="<?= old('nama', $siswaData['nama']) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tempat Lahir</label>
                <input type="text" name="tempat_lahir" value="<?= old('tempat_lahir', $siswaData['tempat_lahir']) ?>">
            </div>
            <div class="form-group">
                <label>Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir"
                    value="<?= old('tanggal_lahir', $siswaData['tanggal_lahir']) ?>">
            </div>
            <div class="form-group">
                <label>Jenis Kelamin</label>
                <select name="jenis_kelamin" required>
                    <option value="L" <?= $siswaData['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= $siswaData['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Agama</label>
                <input type="text" name="agama" value="<?= old('agama', $siswaData['agama']) ?>">
            </div>
            <div class="form-group">
                <label>Email (opsional)</label>
                <input type="email" name="email" value="<?= old('email', $siswaData['email']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="2"><?= old('alamat', $siswaData['alamat']) ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Nama Ayah</label>
                <input type="text" name="nama_ayah" value="<?= old('nama_ayah', $siswaData['nama_ayah']) ?>">
            </div>
            <div class="form-group">
                <label>Nama Ibu</label>
                <input type="text" name="nama_ibu" value="<?= old('nama_ibu', $siswaData['nama_ibu']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Pekerjaan Orang Tua</label>
                <input type="text" name="pekerjaan_ortu"
                    value="<?= old('pekerjaan_ortu', $siswaData['pekerjaan_ortu']) ?>">
            </div>
            <div class="form-group">
                <label>No HP Orang Tua</label>
                <input type="text" name="no_hp_ortu" value="<?= old('no_hp_ortu', $siswaData['no_hp_ortu']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Ganti Foto (opsional)</label>
            <input type="file" name="foto" accept="image/*">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="btnUpdate">Update</button>
            <a href="<?= base_url('admin/siswa') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>