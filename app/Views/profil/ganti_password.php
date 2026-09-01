<?php

$layout = match (session('role')) {
    'Admin' => 'layouts/admin/main',
    'Guru'  => 'layouts/guru/main',
    'Siswa' => 'layouts/siswa/main',
    default => 'layouts/admin/main',
};

?>
<?= $this->extend($layout) ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/ganti-password.css') ?>">

<div class="auth-card-wrapper">
    <div class="auth-card">
        <h2>Ganti Password</h2>
        <p class="auth-subtitle">Perbarui password akun Anda secara berkala.</p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('profil/gantipasswordsubmit') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="password_lama">Password Lama</label>
                <input type="password" id="password_lama" name="password_lama" required>
            </div>
            <div class="form-group">
                <label for="password_baru">Password Baru</label>
                <input type="password" id="password_baru" name="password_baru" minlength="8" required>
                <small class="field-hint">Minimal 8 karakter, mengandung huruf besar dan angka.</small>
            </div>
            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" minlength="6" required>
            </div>
            <button type="submit" class="btn-login">Simpan Password Baru</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>