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


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login - Website Sekolah</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <img src="<?= base_url('assets/images/logo-sekolah.png') ?>" alt="Logo Sekolah" class="login-logo">
            <h2>Masuk ke Akun Anda</h2>

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
    </div>
</body>

</html>