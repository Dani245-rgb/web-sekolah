<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/nilai.css') ?>">

<div class="nilai-card">
    <div class="nilai-header">
        <h4>Rekap Nilai — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>
    </div>
    <p class="nilai-subinfo">KKM: <strong><?= esc($pengaturan['kkm']) ?></strong></p>

    <table class="nilai-siswa-table">
        <thead>
            <tr><th>No</th><th>Nama Siswa</th><th>Nilai Akhir</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($rekap as $r): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($r['nama_siswa']) ?></td>
                <td><?= esc($r['nilai_akhir']) ?></td>
                <td>
                    <span class="nilai-status <?= $r['status'] === 'Tuntas' ? 'nilai-status-tuntas' : 'nilai-status-belum' ?>">
                        <?= esc($r['status']) ?>
                    </span>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rekap)): ?>
            <tr><td colspan="4" class="nilai-empty">Belum ada siswa di kelas ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="nilai-actions">
        <a href="<?= base_url('guru/nilai/form/' . $jadwal['id_jadwal']) ?>" class="nilai-btn nilai-btn-secondary">Kembali ke Input Nilai</a>
        <a href="<?= base_url('guru/dashboard') ?>" class="nilai-btn nilai-btn-secondary">Dashboard</a>
    </div>
</div>

<?= $this->endSection() ?>