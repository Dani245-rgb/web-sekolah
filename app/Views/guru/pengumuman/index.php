<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Pengumuman Sekolah</h4>
    </div>

    <?php if (empty($daftar)): ?>
        <div class="empty-state" style="margin-top:16px;">
            <p>Belum ada pengumuman.</p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:12px; margin-top:16px;">
            <?php foreach ($daftar as $p): ?>
                <a href="<?= base_url('guru/pengumuman/' . $p['slug']) ?>"
                    style="display:block; text-decoration:none; color:inherit; border:1px solid #eee; border-radius:10px; padding:16px 18px; transition:background 0.15s;"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <div style="font-weight:600; font-size:15px; margin-bottom:4px;"><?= esc($p['judul']) ?></div>
                    <div style="font-size:12.5px; color:#888;">
                        <i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($p['tanggal_publish'])) ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:20px;">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>