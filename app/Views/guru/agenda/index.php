<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<?php
$namaHariIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$namaBulanIndo = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];

function formatTanggalAgenda($tanggal, $namaHariIndo, $namaBulanIndo) {
    $ts = strtotime($tanggal);
    return $namaHariIndo[date('l', $ts)] . ', ' . date('d', $ts) . ' ' . $namaBulanIndo[date('F', $ts)] . ' ' . date('Y', $ts);
}
?>

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Agenda Sekolah</h4>
    </div>

    <h5 style="margin-top:20px; color:#4a6cf7;">Akan Datang</h5>
    <?php if (empty($akanDatang)): ?>
        <div class="empty-state" style="margin-top:10px;">
            <p>Tidak ada agenda mendatang.</p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:10px; margin-top:12px;">
            <?php foreach ($akanDatang as $a): ?>
                <?php
                    $ts = strtotime($a['tanggal']);
                    $tgl = date('d', $ts);
                    $bln = strtoupper(substr($namaBulanIndo[date('F', $ts)], 0, 3));
                ?>
                <div style="display:flex; gap:14px; align-items:flex-start; border:1px solid #eee; border-radius:10px; padding:14px 16px;">
                    <div style="flex-shrink:0; width:52px; height:52px; border-radius:8px; background:#eef2ff; color:#4a6cf7;
                        display:flex; flex-direction:column; align-items:center; justify-content:center; font-family:monospace;">
                        <div style="font-size:18px; font-weight:700; line-height:1;"><?= esc($tgl) ?></div>
                        <div style="font-size:10px; letter-spacing:0.05em;"><?= esc($bln) ?></div>
                    </div>
                    <div>
                        <div style="font-weight:600; font-size:14.5px;"><?= esc($a['judul']) ?></div>
                        <div style="font-size:12.5px; color:#888; margin-top:4px;">
                            <?= formatTanggalAgenda($a['tanggal'], $namaHariIndo, $namaBulanIndo) ?>
                            <?php if (!empty($a['waktu'])): ?> &middot; <?= esc(substr($a['waktu'], 0, 5)) ?> WIB<?php endif; ?>
                            <?php if (!empty($a['lokasi'])): ?> &middot; <i class="bi bi-geo-alt"></i> <?= esc($a['lokasi']) ?><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h5 style="margin-top:28px; color:#888;">Sudah Berlalu</h5>
    <?php if (empty($sudahLewat)): ?>
        <div class="empty-state" style="margin-top:10px;">
            <p>Belum ada riwayat agenda.</p>
        </div>
    <?php else: ?>
        <div style="display:flex; flex-direction:column; gap:8px; margin-top:12px; opacity:0.75;">
            <?php foreach ($sudahLewat as $a): ?>
                <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f0f0f0; padding:8px 4px; font-size:13.5px;">
                    <span><?= esc($a['judul']) ?></span>
                    <span style="color:#888;"><?= formatTanggalAgenda($a['tanggal'], $namaHariIndo, $namaBulanIndo) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:24px;">
        <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>