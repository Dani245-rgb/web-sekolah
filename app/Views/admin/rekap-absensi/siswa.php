<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="page-header">
    <h4>Rekap Absensi — Per Siswa</h4>
    <p class="breadcrumb">Dashboard / Rekap Absensi / Siswa</p>
</div>

<div class="card">
    <form action="<?= base_url('admin/rekap-absensi/siswa') ?>" method="get" class="absensi-search-bar">
        <input type="text" name="keyword" placeholder="Cari nama atau NIS siswa..." value="<?= esc($keyword) ?>" class="absensi-search-input">
        <button type="submit" class="btn btn-secondary">Cari</button>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($siswa as $s): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($s['nis']) ?></td>
                    <td><?= esc($s['nama']) ?></td>
                    <td><?= esc($s['status']) ?></td>
                    <td><a href="<?= base_url('admin/rekap-absensi/siswa/detail/' . $s['id_siswa']) ?>" class="btn btn-sm btn-primary">Lihat Rekap</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($siswa)): ?>
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data siswa.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>