<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Jurnal PKL — <?= esc($penempatan['nama_siswa']) ?></h4>
        <span style="color:#888; font-size:14px;"><?= esc($penempatan['nama_perusahaan']) ?></span>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert" style="background:#dcfce7;color:#166534;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($daftarJurnal)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">
            Siswa ini belum mengisi jurnal PKL.
        </div>
    <?php else: ?>
        <?php foreach ($daftarJurnal as $j): ?>
            <?php
                $warna = match ($j['status_validasi']) {
                    'Disetujui' => ['#dcfce7', '#166534'],
                    'Ditolak'   => ['#fee2e2', '#991b1b'],
                    default     => ['#fef9c3', '#854d0e'],
                };
            ?>
            <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-top:14px;">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <div style="font-weight:600;"><?= date('d F Y', strtotime($j['tanggal'])) ?></div>
                    <span style="background:<?= $warna[0] ?>; color:<?= $warna[1] ?>; padding:2px 10px; border-radius:12px; font-size:12px;">
                        <?= esc($j['status_validasi']) ?>
                    </span>
                </div>
                <div style="margin-top:8px; font-size:14px;"><strong>Kegiatan:</strong> <?= nl2br(esc($j['kegiatan'])) ?></div>
                <?php if (!empty($j['kendala'])): ?>
                    <div style="margin-top:6px; font-size:14px;"><strong>Kendala:</strong> <?= nl2br(esc($j['kendala'])) ?></div>
                <?php endif; ?>

                <?php if ($j['status_validasi'] === 'Menunggu'): ?>
                    <form method="post" action="<?= base_url('guru/pkl/jurnal/validasi/' . $j['id_jurnal']) ?>" style="margin-top:12px; display:flex; gap:8px; align-items:center;">
                        <?= csrf_field() ?>
                        <input type="text" name="catatan_pembimbing" placeholder="Catatan (opsional)" style="flex:1; padding:6px 10px; border:1px solid #ddd; border-radius:8px; font-size:13px;">
                        <button type="submit" name="status_validasi" value="Disetujui" class="btn btn-primary" style="padding:5px 14px; font-size:13px;">Setujui</button>
                        <button type="submit" name="status_validasi" value="Ditolak" class="btn" style="padding:5px 14px; font-size:13px; background:#fee2e2; color:#991b1b; border:none; border-radius:8px;">Tolak</button>
                    </form>
                <?php elseif (!empty($j['catatan_pembimbing'])): ?>
                    <div style="margin-top:8px; font-size:13px; color:#888;">Catatan pembimbing: <?= esc($j['catatan_pembimbing']) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('guru/pkl') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>