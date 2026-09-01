<?php
$pengaturanModelLogin = new \App\Models\PengaturanModel();
$pengaturanLogin      = $pengaturanModelLogin->getPengaturan();
$namaSekolahLogin     = $pengaturanLogin['nama_sekolah'] ?? 'Website Sekolah';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= esc($namaSekolahLogin) ?></title>
    <?php if (!empty($pengaturanLogin['favicon'])): ?>
        <link rel="icon" type="image/png" href="<?= base_url('assets/uploads/sekolah/' . $pengaturanLogin['favicon']) ?>">
    <?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
</head>

<body>
    <div class="login-page">

        <!-- PANEL KIRI: identitas sekolah -->
        <aside class="login-aside">
            <div class="aside-brand">
                <?php if (!empty($pengaturanLogin['logo'])): ?>
                    <img src="<?= base_url('assets/uploads/sekolah/' . $pengaturanLogin['logo']) ?>" alt="Logo <?= esc($namaSekolahLogin) ?>">
                <?php endif; ?>
                <span><?= esc(strtoupper($namaSekolahLogin)) ?></span>
            </div>

            <div class="aside-content">
                <span class="aside-eyebrow">Portal Akademik Terpadu</span>
                <h1>Satu Akun untuk Admin, Guru, dan Siswa</h1>
                <p>Kelola data akademik, absensi, nilai, dan informasi sekolah dalam satu tempat yang aman dan terpercaya.</p>
            </div>

            <div class="aside-footer">
                &copy; <?= date('Y') ?> <?= esc($namaSekolahLogin) ?>. Seluruh hak dilindungi.
            </div>
        </aside>

        <!-- PANEL KANAN: form login -->
        <main class="login-main">
            <div class="login-box">
                <h2><?= esc($namaSekolahLogin) ?></h2>
                <p class="login-subtitle">Masuk ke Akun Anda</p>

                <?php
                // Satukan semua sumber flash message jadi satu array, hindari duplikasi/konflik style
                $pesanError = session()->getFlashdata('errors') ?? [];
                if (session()->getFlashdata('error')) {
                    $pesanError[] = session()->getFlashdata('error');
                }
                $pesanInfo = session()->getFlashdata('info');
                ?>

                <?php if (!empty($pesanError)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($pesanError as $error): ?>
                            <div><?= esc($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($pesanInfo): ?>
                    <div class="alert alert-success">
                        <?= esc($pesanInfo) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="post" id="form-login">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" autofocus required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                            <button type="button" class="toggle-password" id="toggle-password" aria-label="Tampilkan password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label><input type="checkbox" name="remember"> Ingat saya</label>
                        <a href="<?= base_url('forgot-password') ?>">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn-login" id="btn-submit-login">Masuk</button>
                </form>
            </div>
        </main>

    </div>

    <script>
        // Toggle show/hide password
        document.getElementById('toggle-password').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
            this.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
        });

        // Loading state saat submit
        document.getElementById('form-login').addEventListener('submit', function () {
            const btn = document.getElementById('btn-submit-login');
            btn.classList.add('btn-loading');
            btn.disabled = true;
        });
    </script>
</body>

</html>