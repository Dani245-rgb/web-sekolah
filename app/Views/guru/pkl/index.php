<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Siswa Bimbingan PKL</h4>
    </div>

    <?php if (empty($daftar)): ?>
        <div style="text-align:center; padding:40px 0; color:#999;">
            Belum ada siswa PKL yang Anda bimbing.
        </div>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; margin-top:16px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left;">
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Siswa</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Perusahaan</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Periode</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Status</th>
                    <th style="padding:10px 12px; font-size:13px; color:#888;">Jurnal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daftar as $d): ?>
                    <tr style="border-bottom:1px solid #eee;">
                        <td style="padding:10px 12px;"><?= esc($d['nama_siswa']) ?> <br><small style="color:#888;"><?= esc($d['nis']) ?></small></td>
                        <td style="padding:10px 12px;"><?= esc($d['nama_perusahaan']) ?></td>
                        <td style="padding:10px 12px;"><?= date('d/m/Y', strtotime($d['tanggal_mulai'])) ?> - <?= $d['tanggal_selesai'] ? date('d/m/Y', strtotime($d['tanggal_selesai'])) : '-' ?></td>
                        <td style="padding:10px 12px;"><?= esc($d['status']) ?></td>
                        <td style="padding:10px 12px;">
                            <a href="<?= base_url('guru/pkl/jurnal/' . $d['id_penempatan']) ?>" class="btn btn-secondary" style="padding:4px 12px; font-size:13px;">Lihat Jurnal</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:20px;">
        <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>