<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Jadwal Pelajaran</h4>
        <?php if (!empty($namaKelas)): ?>
            <span style="color:#888; font-size:14px;">Kelas <?= esc($namaKelas) ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($pesan)): ?>
        <div class="alert" style="background:#fef9c3;color:#854d0e;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc($pesan) ?>
        </div>
    <?php else: ?>

        <?php if ($totalJadwal === 0): ?>
            <div style="text-align:center; padding:40px 0; color:#999;">
                Belum ada jadwal pelajaran untuk kelas Anda.
            </div>
        <?php else: ?>
            <?php foreach ($jadwalPerHari as $hari => $daftar): ?>
                <?php if (empty($daftar)) continue; ?>
                <div style="margin-top:20px;">
                    <h5 style="margin-bottom:8px; color:#4a6cf7;"><?= esc($hari) ?></h5>
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f8fafc; text-align:left;">
                                <th style="padding:8px 10px; font-size:13px; color:#888;">Jam</th>
                                <th style="padding:8px 10px; font-size:13px; color:#888;">Mata Pelajaran</th>
                                <th style="padding:8px 10px; font-size:13px; color:#888;">Guru</th>
                                <th style="padding:8px 10px; font-size:13px; color:#888;">Ruangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daftar as $j): ?>
                                <tr style="border-bottom:1px solid #eee;">
                                    <td style="padding:8px 10px; font-weight:600;">
                                        <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?>
                                    </td>
                                    <td style="padding:8px 10px;"><?= esc($j['nama_mapel']) ?></td>
                                    <td style="padding:8px 10px;"><?= esc($j['nama_guru']) ?></td>
                                    <td style="padding:8px 10px;"><?= esc($j['nama_ruangan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:24px;">
        <a href="<?= base_url('siswa/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>