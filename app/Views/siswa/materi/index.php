<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <h4>Download Materi</h4>

    <?php if (!empty($errors)): ?>
        <div class="alert" style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc($errors) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($daftar)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">Belum ada materi yang diunggah untuk kelas Anda.</div>
    <?php else: ?>
        <?php foreach ($daftar as $m): ?>
            <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-top:14px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-weight:600;"><?= esc($m['judul']) ?></div>
                    <div style="font-size:13px; color:#888; margin-top:2px;"><?= esc($m['nama_mapel']) ?> · <?= esc($m['nama_guru']) ?> · <?= date('d/m/Y', strtotime($m['created_at'])) ?></div>
                    <?php if (!empty($m['deskripsi'])): ?>
                        <div style="font-size:13.5px; margin-top:6px;"><?= nl2br(esc($m['deskripsi'])) ?></div>
                    <?php endif; ?>
                </div>
                <a href="<?= base_url('materi/unduh/' . $m['id_materi']) ?>" class="btn btn-primary" style="white-space:nowrap;">Download</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>