<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4><?= esc($pengumuman['judul']) ?></h4>
    </div>

    <div style="font-size:12.5px; color:#888; margin-top:4px;">
        Dipublikasikan: <?= date('d F Y', strtotime($pengumuman['tanggal_publish'])) ?>
    </div>

    <div style="margin-top:16px; line-height:1.7;">
        <?= $pengumuman['isi'] ?>
    </div>

    <div class="absensi-actions" style="margin-top:24px;">
        <a href="<?= base_url('siswa/pengumuman') ?>" class="btn btn-secondary">Kembali ke Daftar</a>
    </div>
</div>

<?= $this->endSection() ?>