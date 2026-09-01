<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <h4>Tugas Saya</h4>

    <?php if (!empty($errors)): ?>
        <div class="alert" style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;margin-top:16px;">
            <?= esc($errors) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($daftar)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">Belum ada tugas untuk saat ini.</div>
    <?php else: ?>
        <?php
        $warnaBadge = [
            'Belum Kumpul'  => ['#fef9c3', '#854d0e'],
            'Menunggu'      => ['#dbeafe', '#1e40af'],
            'Dinilai'       => ['#dcfce7', '#166534'],
            'Terlambat'     => ['#fee2e2', '#991b1b'],
            'Lewat Tenggat' => ['#fee2e2', '#991b1b'],
        ];
        ?>
        <?php foreach ($daftar as $t): $warna = $warnaBadge[$t['submisi_status']] ?? ['#f1f5f9', '#334155']; ?>
            <a href="<?= base_url('siswa/tugas/detail/' . $t['id_tugas']) ?>" style="display:block; text-decoration:none; color:inherit;">
                <div style="border:1px solid #eee; border-radius:10px; padding:16px; margin-top:14px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-weight:600;"><?= esc($t['judul']) ?></div>
                        <div style="font-size:13px; color:#888; margin-top:2px;"><?= esc($t['nama_mapel']) ?> · Tenggat: <?= date('d/m/Y H:i', strtotime($t['tenggat'])) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <span style="background:<?= $warna[0] ?>; color:<?= $warna[1] ?>; padding:3px 12px; border-radius:12px; font-size:12.5px;">
                            <?= esc($t['submisi_status']) ?>
                        </span>
                        <?php if ($t['submisi_nilai'] !== null): ?>
                            <div style="font-size:13px; margin-top:4px; font-weight:600;">Nilai: <?= esc($t['submisi_nilai']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>