<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <h4>Data Kelas</h4>

    <?php if (empty($daftar)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">Belum ada kelas yang Anda ajar.</div>
    <?php else: ?>
        <div style="display:flex; flex-wrap:wrap; gap:14px; margin-top:16px;">
            <?php foreach ($daftar as $d): ?>
                <a href="<?= base_url('guru/kelas/siswa/' . $d['id_kelas'] . '/' . $d['id_mapel']) ?>"
                    style="text-decoration:none; color:inherit; flex:1 1 260px; border:1px solid #eee; border-radius:12px; padding:18px; display:block;">
                    <div style="font-weight:600; font-size:16px;"><?= esc($d['nama_kelas']) ?></div>
                    <div style="font-size:13.5px; color:#888; margin-top:4px;"><?= esc($d['nama_mapel']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>