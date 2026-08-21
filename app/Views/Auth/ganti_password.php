<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Ganti Password - Website Sekolah</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <img src="<?= base_url('assets/images/logo-sekolah.png') ?>" alt="Logo Sekolah" class="login-logo">
            <h2>Ganti Password</h2>
            <p>Kamu wajib mengganti password sebelum melanjutkan.</p>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="alert alert-error" style="background:#EAE7EA; color:#8B6F00; border-color:#E0C88B;">
                    <?= esc(session()->getFlashdata('warning')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-error">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('auth/gantipasswordsubmit') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="password_baru">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru" minlength="8" required>
                    <small style="color:#888; font-size:12px; display:block; margin-top:4px;">
                        Minimal 8 karakter, mengandung huruf besar dan angka.
                    </small>
                </div>

                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" minlength="6" required>
                </div>

                <button type="submit" class="btn-login">Simpan Password Baru</button>
            </form>
        </div>
    </div>
</body>

</html>