<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Absensi Saya</h4>
    </div>

    <form method="get" action="<?= base_url('siswa/absensi') ?>" style="margin-top:16px; display:flex; gap:10px; align-items:center;">
        <label style="font-weight:600; font-size:14px;">Pilih Bulan:</label>
        <input type="month" name="bulan" value="<?= esc($bulanDipilih) ?>" class="absensi-keterangan" onchange="this.form.submit()">
    </form>

    <div style="display:flex; gap:16px; margin-top:20px; flex-wrap:wrap;">
        <div style="flex:1; min-width:120px; background:#dcfce7; border-radius:10px; padding:16px; text-align:center;">
            <div style="font-size:24px; font-weight:700; color:#166534;"><?= $rekap['Hadir'] ?></div>
            <div style="font-size:13px; color:#166534;">Hadir</div>
        </div>
        <div style="flex:1; min-width:120px; background:#fef9c3; border-radius:10px; padding:16px; text-align:center;">
            <div style="font-size:24px; font-weight:700; color:#854d0e;"><?= $rekap['Izin'] ?></div>
            <div style="font-size:13px; color:#854d0e;">Izin</div>
        </div>
        <div style="flex:1; min-width:120px; background:#dbeafe; border-radius:10px; padding:16px; text-align:center;">
            <div style="font-size:24px; font-weight:700; color:#1e40af;"><?= $rekap['Sakit'] ?></div>
            <div style="font-size:13px; color:#1e40af;">Sakit</div>
        </div>
        <div style="flex:1; min-width:120px; background:#fee2e2; border-radius:10px; padding:16px; text-align:center;">
            <div style="font-size:24px; font-weight:700; color:#991b1b;"><?= $rekap['Alfa'] ?></div>
            <div style="font-size:13px; color:#991b1b;">Alfa</div>
        </div>
        <div style="flex:1; min-width:120px; background:#eef2ff; border-radius:10px; padding:16px; text-align:center;">
            <div style="font-size:24px; font-weight:700; color:#4a6cf7;"><?= $persenHadir ?>%</div>
            <div style="font-size:13px; color:#4a6cf7;">Persentase Hadir</div>
        </div>
    </div>

    <h5 style="margin-top:24px; margin-bottom:10px;">Riwayat Detail</h5>

    <?php if (empty($riwayat)): ?>
        <div style="text-align:center; padding:30px 0; color:#999;">
            Belum ada data absensi pada bulan ini.
        </div>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc; text-align:left;">
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Tanggal</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Mata Pelajaran</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Status</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riwayat as $r): ?>
                    <?php
                        $warna = match ($r['status']) {
                            'Hadir' => ['#dcfce7', '#166534'],
                            'Izin'  => ['#fef9c3', '#854d0e'],
                            'Sakit' => ['#dbeafe', '#1e40af'],
                            'Alfa'  => ['#fee2e2', '#991b1b'],
                            default => ['#f1f5f9', '#64748b'],
                        };
                    ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:9px 12px;"><?= date('d F Y', strtotime($r['tanggal'])) ?></td>
                        <td style="padding:9px 12px;"><?= esc($r['nama_mapel']) ?></td>
                        <td style="padding:9px 12px;">
                            <span style="background:<?= $warna[0] ?>; color:<?= $warna[1] ?>; padding:2px 10px; border-radius:12px; font-size:12px;">
                                <?= esc($r['status']) ?>
                            </span>
                        </td>
                        <td style="padding:9px 12px; color:#888;"><?= esc($r['keterangan'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('siswa/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>