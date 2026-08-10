<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/kenaikan-kelas.css') ?>">

<div class="page-header">
    <h4>Kenaikan Kelas</h4>
    <p class="breadcrumb">Dashboard / Kenaikan Kelas</p>
</div>

<div class="card kenaikan-kelas-intro">
    <p>Proses kenaikan kelas memindahkan siswa dari kelas asal (tahun ajaran berjalan) ke kelas tujuan (tahun ajaran baru).</p>
    <p class="text-muted">
        Setelah diproses, hasilnya bisa dicek di menu
        <a href="<?= base_url('admin/assign-kelas') ?>" class="link-inline">Assign Kelas</a>.
    </p>
    <a href="<?= base_url('admin/kenaikan-kelas/form') ?>" class="btn btn-primary">+ Proses Kenaikan Kelas</a>
</div>

<?= $this->endSection() ?>