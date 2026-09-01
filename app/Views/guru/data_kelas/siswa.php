<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <h4>Data Kelas — <?= esc($kelasInfo['nama_kelas']) ?> / <?= esc($kelasInfo['nama_mapel']) ?></h4>

    <?php if (empty($daftarSiswa)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">Belum ada siswa di kelas ini.</div>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; margin-top:16px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left;">
                    <th style="padding:10px 12px;">Nama</th>
                    <th style="padding:10px 12px;">NIS</th>
                    <th style="padding:10px 12px;">Rata-rata Nilai</th>
                    <th style="padding:10px 12px;">Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftarSiswa as $s): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px 12px; font-weight:600;"><?= esc($s['nama']) ?></td>
                        <td style="padding:10px 12px;"><?= esc($s['nis']) ?></td>
                        <td style="padding:10px 12px;">
                            <?= $s['rata_nilai'] !== null ? esc($s['rata_nilai']) : '<span style="color:#999;">Belum ada</span>' ?>
                        </td>
                        <td style="padding:10px 12px;">
                            <?php if ($s['persen_hadir'] === null): ?>
                                <span style="color:#999;">Belum ada data</span>
                            <?php else: ?>
                                <span style="background:<?= $s['persen_hadir'] >= 80 ? '#dcfce7' : '#fee2e2' ?>; color:<?= $s['persen_hadir'] >= 80 ? '#166534' : '#991b1b' ?>; padding:3px 10px; border-radius:12px;">
                                    <?= $s['persen_hadir'] ?>%
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('guru/kelas') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>