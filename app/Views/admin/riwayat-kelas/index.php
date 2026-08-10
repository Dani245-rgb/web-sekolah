<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Riwayat Kelas Siswa</h4>
    <p class="breadcrumb">Dashboard / Riwayat Kelas</p>
</div>

<div class="card">
    <form action="<?= base_url('admin/riwayat-kelas') ?>" method="get" class="search-form" style="margin-bottom:16px;">
        <input type="text" name="keyword" placeholder="Cari nama atau NIS siswa..." value="<?= esc($keyword) ?>">
        <button type="submit" class="btn btn-secondary">Cari</button>
    </form>

    <table class="table">
        <thead>
            <tr><th>No</th><th>NIS</th><th>Nama</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($siswa as $s): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($s['nis']) ?></td>
                <td><?= esc($s['nama']) ?></td>
                <td><?= esc($s['status']) ?></td>
                <td>
                    <a href="<?= base_url('admin/riwayat-kelas/detail/' . $s['id_siswa']) ?>" class="btn btn-sm btn-primary">Lihat Riwayat</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($siswa)): ?>
            <tr><td colspan="5" class="text-center">Tidak ada data siswa.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>