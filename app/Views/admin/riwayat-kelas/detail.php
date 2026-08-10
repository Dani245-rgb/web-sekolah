<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Riwayat Kelas — <?= esc($siswa['nama']) ?></h4>
    <p class="breadcrumb">Dashboard / Riwayat Kelas / Detail</p>
</div>

<div class="card">
    <p><strong>NIS:</strong> <?= esc($siswa['nis']) ?> &nbsp;|&nbsp; <strong>Status saat ini:</strong> <?= esc($siswa['status']) ?></p>

    <table class="table" style="margin-top:16px;">
        <thead>
            <tr><th>Tahun Ajaran</th><th>Kelas</th></tr>
        </thead>
        <tbody>
            <?php foreach ($riwayat as $r): ?>
            <tr>
                <td><?= esc($r['tahun_ajaran']) ?></td>
                <td><?= esc($r['nama_kelas']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($riwayat)): ?>
            <tr><td colspan="2" class="text-center">Belum ada riwayat kelas untuk siswa ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="<?= base_url('admin/riwayat-kelas') ?>" class="btn btn-secondary" style="margin-top:16px;">Kembali</a>
</div>

<?= $this->endSection() ?>