<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard_siswa/profil.css') ?>">

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

    <form action="<?= base_url('siswa/profil/update') ?>" method="post" enctype="multipart/form-data" style="max-width:600px; margin-top:16px;">
        <?= csrf_field() ?>

        <div class="profil-form-group">
            <label class="profil-label">Foto Profil</label>
            <?php if (!empty($siswa['foto'])): ?>
                <img src="<?= base_url('assets/uploads/siswa/' . $siswa['foto']) ?>" alt="Foto saat ini" class="profil-avatar-edit-preview">
            <?php endif; ?>
            <input type="file" name="foto" accept="image/png, image/jpeg" class="profil-input">
            <div class="profil-help-text">Format JPG/PNG, maksimal 2MB. Kosongkan jika tidak ingin mengubah foto.</div>
        </div>

        <div class="profil-form-group">
            <label class="profil-label">Alamat</label>
            <textarea name="alamat" rows="3" class="profil-textarea"><?= old('alamat', $siswa['alamat']) ?></textarea>
        </div>

        <div class="profil-form-row">
            <div class="profil-form-group">
                <label class="profil-label">No. HP Orang Tua</label>
                <input type="text" name="no_hp_ortu" value="<?= old('no_hp_ortu', $siswa['no_hp_ortu']) ?>" class="profil-input">
            </div>
            <div class="profil-form-group">
                <label class="profil-label">Email</label>
                <input type="email" name="email" value="<?= old('email', $siswa['email']) ?>" class="profil-input">
            </div>
        </div>

        <div class="profil-readonly-note">
            Data identitas (NIS, NISN, Nama, Kelas, Tempat/Tanggal Lahir, Data Orang Tua) hanya dapat diubah oleh Admin.
        </div>

        <div class="absensi-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('siswa/profil') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>