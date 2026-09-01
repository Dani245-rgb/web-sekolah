<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Riwayat Kelas Siswa</h4>
    <p class="breadcrumb">Dashboard / Riwayat Kelas</p>
</div>

<div class="card">
    <div class="card-toolbar">
        <form action="<?= base_url('admin/riwayat-kelas') ?>" method="get" class="search-form">
            <input type="text" name="keyword" placeholder="Cari nama atau NIS siswa..." value="<?= esc($keyword) ?>">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

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
                    <td>
                        <?php
                        $badgeStatus = match ($s['status']) {
                            'Aktif' => 'badge-success',
                            'Lulus' => 'badge-warning',
                            default => 'badge-danger',
                        };
                        ?>
                        <span class="badge <?= $badgeStatus ?>"><?= esc($s['status']) ?></span>
                    </td>
                    <td>
                        <a href="<?= base_url('admin/riwayat-kelas/detail/' . $s['id_siswa']) ?>" class="btn btn-sm btn-primary">Lihat Riwayat</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($siswa)): ?>
                <tr>
                    <td colspan="5" class="text-center">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i class="bi bi-inbox" style="font-size:24px;color:#d7dce3;"></i>
                            <span>Tidak ada data siswa.</span>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>