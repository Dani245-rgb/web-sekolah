<?php

$layout = match (session('role')) {
    'Admin' => 'layouts/admin',
    'Guru'  => 'layouts/guru',
    'Siswa' => 'layouts/siswa',
    default => 'layouts/admin',
};

?>
<?= $this->extend($layout) ?>
<?= $this->section('content') ?>

<h2>Ganti Password</h2>

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
        <small style="color:#888; font-size:12px; display:block; margin-top:4px;">
            Minimal 8 karakter, mengandung huruf besar dan angka.
        </small>
    </div>
    <div class="form-group">
        <label for="konfirmasi_password">Konfirmasi Password Baru</label>
        <input type="password" id="konfirmasi_password" name="konfirmasi_password" minlength="6" required>
    </div>
    <button type="submit" class="btn-login">Simpan Password Baru</button>
</form>

<?= $this->section('endSection') ?>