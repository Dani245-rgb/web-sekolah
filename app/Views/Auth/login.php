<?php
$pengaturanModelLogin = new \App\Models\PengaturanModel();
$pengaturanLogin      = $pengaturanModelLogin->getPengaturan();
$namaSekolahLogin     = $pengaturanLogin['nama_sekolah'] ?? 'Website Sekolah';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login - <?= esc($namaSekolahLogin) ?></title>
    <?php if (!empty($pengaturanLogin['favicon'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('assets/uploads/sekolah/' . $pengaturanLogin['favicon']) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
</head>

<body>
    <main class="login-main" style="min-height:100vh;">
        <div class="login-box">
            <?php if (!empty($pengaturanLogin['logo'])): ?>
                <img src="<?= base_url('assets/uploads/sekolah/' . $pengaturanLogin['logo']) ?>" alt="Logo Sekolah" style="height:48px;display:block;margin:0 auto 20px;">
            <?php endif; ?>

            <h2 style="text-align:center;"><?= esc($namaSekolahLogin) ?></h2>
            <p class="login-subtitle" style="text-align:center;">Masuk ke Akun Anda</p>

            <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('info')): ?>
            <div class="alert alert-error" style="background:#EAF7EA; color:#2E7D32; border-color:#B8E0B8;">
                <?= esc(session()->getFlashdata('info')) ?>
            </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error">
                <?= session()->getFlashdata('error') ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <div class="form-options">
                    <label><input type="checkbox" name="remember"> Ingat saya</label>
                    <a href="<?= base_url('forgot-password') ?>">Lupa password?</a>
                </div>

                <button type="submit" class="btn-login">Masuk</button>
            </form>
        </div>
    </main>
</body>

</html>