<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard_guru/profil.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Edit Profil</h4>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert" style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('guru/profil/update') ?>" method="post" enctype="multipart/form-data" style="max-width:600px; margin-top:16px;">
        <?= csrf_field() ?>

        <div class="profil-form-group">
            <label class="profil-label">Foto Profil</label>
            <?php if (!empty($guru['foto'])): ?>
                <img src="<?= base_url('assets/uploads/guru/' . $guru['foto']) ?>" alt="Foto saat ini" class="profil-avatar-edit-preview">
            <?php endif; ?>
            <input type="file" name="foto" accept="image/png, image/jpeg" class="profil-input">
            <div class="profil-help-text">Format JPG/PNG, maksimal 2MB. Kosongkan jika tidak ingin mengubah foto.</div>
        </div>

        <div class="profil-form-group">
            <label class="profil-label">Nama Lengkap</label>
            <input type="text" name="nama" value="<?= old('nama', $guru['nama']) ?>" class="profil-input" required>
        </div>

        <div class="profil-form-row">
            <div class="profil-form-group">
                <label class="profil-label">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" value="<?= old('tempat_lahir', $guru['tempat_lahir']) ?>" class="profil-input">
            </div>
            <div class="profil-form-group">
                <label class="profil-label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir', $guru['tanggal_lahir']) ?>" class="profil-input">
            </div>
        </div>

        <div class="profil-form-group">
            <label class="profil-label">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="profil-select" required>
                <option value="L" <?= old('jenis_kelamin', $guru['jenis_kelamin']) === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="P" <?= old('jenis_kelamin', $guru['jenis_kelamin']) === 'P' ? 'selected' : '' ?>>Perempuan</option>
            </select>
        </div>

        <div class="profil-form-row">
            <div class="profil-form-group">
                <label class="profil-label">No. HP</label>
                <input type="text" name="no_hp" value="<?= old('no_hp', $guru['no_hp']) ?>" class="profil-input">
            </div>
            <div class="profil-form-group">
                <label class="profil-label">Email</label>
                <input type="email" name="email" value="<?= old('email', $guru['email']) ?>" class="profil-input">
            </div>
        </div>

        <div class="profil-readonly-note">
            NIP, NUPTK, Jabatan, dan Status kepegawaian hanya dapat diubah oleh Admin.
        </div>

        <div class="absensi-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('guru/profil') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>