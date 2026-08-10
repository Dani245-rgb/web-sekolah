<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Siswa di Kelas <?= esc($kelas['nama_kelas']) ?></h4>
    <p class="breadcrumb">Dashboard / Kelas / Siswa</p>
</div>

<div class="card">
    <?php if ($tahunAktif): ?>
    <p class="text-muted">Menampilkan siswa untuk Tahun Ajaran aktif: <strong><?= esc($tahunAktif['tahun_ajaran']) ?></strong></p>
    <?php else: ?>
    <div class="alert alert-error">Belum ada Tahun Ajaran aktif.</div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr><th>No</th><th>NIS</th><th>Nama</th></tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($siswa as $s): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($s['nis']) ?></td>
                <td><?= esc($s['nama']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($siswa)): ?>
            <tr><td colspan="3" class="text-center">Belum ada siswa di kelas ini untuk tahun ajaran aktif.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="<?= base_url('admin/kelas') ?>" class="btn btn-secondary" style="margin-top:16px;">Kembali</a>
</div>

<?= $this->endSection() ?>