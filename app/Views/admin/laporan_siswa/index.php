<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;">
    <div>
        <h4>Laporan Siswa</h4>
        <p class="breadcrumb">Dashboard / Laporan Siswa</p>
    </div>
    <a href="<?= base_url('admin/laporan-siswa/export/excel') ?>" class="btn btn-primary"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
</div>

<div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:24px;">
    <div class="card" style="flex:1;min-width:200px;">
        <strong style="color:#7c8a9c;font-size:12px;">Total Siswa</strong>
        <h3 style="margin-top:6px;font-size:28px;color:#3b82f6;"><?= $totalSiswa ?></h3>
    </div>
    <div class="card" style="flex:1;min-width:200px;">
        <strong>Per Gender</strong>
        <div style="margin-top:6px;font-size:13px;">Laki-laki: <strong><?= $totalPerGender['L'] ?></strong></div>
        <div style="font-size:13px;">Perempuan: <strong><?= $totalPerGender['P'] ?></strong></div>
    </div>
</div>

<div class="card">
    <div class="table-responsive-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>NIS</th>
                    <th>NISN</th>
                    <th>Nama</th>
                    <th>Jenis Kelamin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswa as $s): ?>
                    <tr>
                        <td><?= esc($s['nis']) ?></td>
                        <td><?= esc($s['nisn']) ?></td>
                        <td><?= esc($s['nama']) ?></td>
                        <td><?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($siswa)): ?>
                    <tr>
                        <td colspan="4" class="text-center">
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
</div>

<?= $this->endSection() ?>