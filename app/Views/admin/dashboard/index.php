<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Dashboard</h4>
    <p class="breadcrumb">Selamat datang, <?= esc(session()->get('username')) ?></p>
</div>

<div class="card">
    <p>Halaman dashboard admin. Widget statistik (jumlah siswa, guru, kelas) akan ditambahkan setelah modul-modul
        terkait selesai.</p>
</div>

<?= $this->endSection() ?>