<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Nilai Saya</h4>
        <?php if (!empty($semesterAktif)): ?>
            <span style="color:#888; font-size:14px;"><?= esc($semesterAktif['nama_semester']) ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($pesan)): ?>
        <div class="alert" style="background:#fef9c3;color:#854d0e;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc($pesan) ?>
        </div>
    <?php elseif (empty($hasil)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">
            Belum ada mata pelajaran yang memerlukan nilai pada semester ini.
        </div>
    <?php else: ?>

        <table style="width:100%; border-collapse:collapse; margin-top:16px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left;">
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Mata Pelajaran</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">KKM</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Nilai Akhir</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hasil as $row): ?>
                    <?php
                        $warna = match ($row['status']) {
                            'Tuntas'        => ['#dcfce7', '#166534'],
                            'Belum Tuntas'  => ['#fee2e2', '#991b1b'],
                            'Belum Lengkap' => ['#fef9c3', '#854d0e'],
                            default         => ['#f1f5f9', '#64748b'],
                        };
                    ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px 12px; font-weight:600;"><?= esc($row['mapel']) ?></td>
                        <td style="padding:10px 12px;"><?= $row['kkm'] !== null ? esc($row['kkm']) : '-' ?></td>
                        <td style="padding:10px 12px; font-weight:700; color:#4a6cf7;">
                            <?= $row['nilai_akhir'] !== null ? number_format($row['nilai_akhir'], 2) : '-' ?>
                        </td>
                        <td style="padding:10px 12px;">
                            <span style="background:<?= $warna[0] ?>; color:<?= $warna[1] ?>; padding:2px 10px; border-radius:12px; font-size:12px;">
                                <?= esc($row['status']) ?>
                            </span>
                        </td>
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