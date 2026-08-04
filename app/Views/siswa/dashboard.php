<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Siswa - Website Sekolah</title>
</head>

<body style="font-family: sans-serif; padding: 40px;">
    <h1>Selamat datang, <?= esc($nama) ?>!</h1>
    <p><em>Halaman ini masih placeholder — portal siswa (nilai, absensi, jadwal, dll) bisa dikembangkan nanti sebagai
            tahap terpisah.</em></p>
    <p>
        <a href="<?= base_url('profil/gantipassword') ?>">Ganti Password</a> |
        <a href="<?= base_url('logout') ?>">Logout</a>
    </p>
</body>

</html>