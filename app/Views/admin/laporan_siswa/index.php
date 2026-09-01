<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;">
    <div>
        <h4>Laporan Siswa</h4>
        <p class="breadcrumb">Dashboard / Laporan Siswa</p>
    </div>
    <a href="<?= base_url('admin/laporan-siswa/export/excel' . (!empty($statusFilter) ? '?status=' . $statusFilter : '')) ?>" class="btn btn-primary"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
</div>

<div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:24px;">
    <div class="card" style="flex:1;min-width:200px;">
        <strong style="color:#7c8a9c;font-size:12px;">Total Siswa</strong>
        <h3 style="margin-top:6px;font-size:28px;color:#3b82f6;"><?= $totalSiswa ?></h3>
    </div>
    <div class="card" style="flex:1;min-width:200px;">
        <strong>Per Status</strong>
        <?php foreach ($totalPerStatus as $st => $jml): ?>
            <div style="margin-top:6px;font-size:13px;"><?= esc($st) ?>: <strong><?= $jml ?></strong></div>
        <?php endforeach; ?>
    </div>
    <div class="card" style="flex:1;min-width:200px;">
        <strong>Per Jurusan</strong>
        <?php foreach ($totalPerJurusan as $jur => $jml): ?>
            <div style="margin-top:6px;font-size:13px;"><?= esc($jur) ?>: <strong><?= $jml ?></strong></div>
        <?php endforeach; ?>
    </div>
    <div class="card" style="flex:1;min-width:200px;">
        <strong>Per Gender</strong>
        <div style="margin-top:6px;font-size:13px;">Laki-laki: <strong><?= $totalPerGender['L'] ?></strong></div>
        <div style="font-size:13px;">Perempuan: <strong><?= $totalPerGender['P'] ?></strong></div>
    </div>
</div>

<div style="margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?= base_url('admin/laporan-siswa') ?>" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-secondary' ?>">Semua</a>
    <a href="<?= base_url('admin/laporan-siswa?status=Aktif') ?>" class="btn btn-sm <?= $statusFilter === 'Aktif' ? 'btn-primary' : 'btn-secondary' ?>">Aktif</a>
    <a href="<?= base_url('admin/laporan-siswa?status=Lulus') ?>" class="btn btn-sm <?= $statusFilter === 'Lulus' ? 'btn-primary' : 'btn-secondary' ?>">Lulus</a>
    <a href="<?= base_url('admin/laporan-siswa?status=Pindah') ?>" class="btn btn-sm <?= $statusFilter === 'Pindah' ? 'btn-primary' : 'btn-secondary' ?>">Pindah</a>
    <a href="<?= base_url('admin/laporan-siswa?status=Keluar') ?>" class="btn btn-sm <?= $statusFilter === 'Keluar' ? 'btn-primary' : 'btn-secondary' ?>">Keluar</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>NIS</th>
                <th>NISN</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Kelas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($siswa as $s): ?>
                <tr>
                    <td><?= esc($s['nis']) ?></td>
                    <td><?= esc($s['nisn']) ?></td>
                    <td><?= esc($s['nama']) ?></td>
                    <td><?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                    <td><?= esc($s['nama_kelas'] ?? '-') ?></td>
                    <td>
                        <span class="badge <?= $s['status'] === 'Aktif' ? 'badge-success' : ($s['status'] === 'Keluar' || $s['status'] === 'Pindah' ? 'badge-danger' : 'badge-warning') ?>">
                            <?= esc($s['status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($siswa)): ?>
                <tr>
                    <td colspan="6" class="text-center">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i class="bi bi-inbox" style="font-size:24px;color:#d7dce3;"></i>
                            <span>Tidak ada data.</span>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>