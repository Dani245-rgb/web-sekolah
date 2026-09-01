<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4><?= esc($pengumuman['judul']) ?></h4>
    </div>
    <p class="absensi-tanggal">
        <i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($pengumuman['tanggal_publish'])) ?>
    </p>

    <div style="margin-top:20px; line-height:1.7; font-size:14.5px;">
        <?= $pengumuman['isi'] ?>
    </div>

    <div class="absensi-actions" style="margin-top:24px;">
        <a href="<?= base_url('guru/pengumuman') ?>" class="btn btn-secondary">Kembali ke Daftar Pengumuman</a>
    </div>
</div>

<?= $this->endSection() ?>