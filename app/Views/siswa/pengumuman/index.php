<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Pengumuman</h4>
    </div>

    <?php if (empty($daftar)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">
            Belum ada pengumuman.
        </div>
    <?php else: ?>
        <div style="margin-top:16px;">
            <?php foreach ($daftar as $p): ?>
                <a href="<?= base_url('siswa/pengumuman/' . $p['slug']) ?>" style="text-decoration:none; color:inherit;">
                    <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-bottom:12px;">
                        <div style="font-weight:600; font-size:15px; margin-bottom:4px;"><?= esc($p['judul']) ?></div>
                        <div style="font-size:12.5px; color:#888;"><?= date('d F Y', strtotime($p['tanggal_publish'])) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:16px;">
            <?= $pager->links('default', 'default_full') ?>
        </div>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('siswa/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>