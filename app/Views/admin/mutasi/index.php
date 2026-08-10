<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Data Mutasi Siswa</h4>
    <p class="breadcrumb">Dashboard / Mutasi</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card">
    <a href="<?= base_url('admin/mutasi/form') ?>" class="btn btn-primary" style="margin-bottom:16px;">+ Catat Mutasi</a>

    <table class="table">
        <thead>
            <tr>
                <th>No</th><th>NIS</th><th>Nama</th><th>Jenis</th><th>Tahun Ajaran</th><th>Tanggal</th><th>Sekolah Tujuan</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($mutasi as $m): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($m['nis']) ?></td>
                <td><?= esc($m['nama']) ?></td>
                <td>
                    <span class="badge <?= $m['jenis_mutasi'] === 'Pindah' ? 'badge-warning' : 'badge-danger' ?>">
                        <?= esc($m['jenis_mutasi']) ?>
                    </span>
                </td>
                <td><?= esc($m['tahun_ajaran']) ?></td>
                <td><?= esc($m['tanggal_mutasi']) ?></td>
                <td><?= esc($m['sekolah_tujuan'] ?? '-') ?></td>
                <td>
                    <a href="<?= base_url('admin/mutasi/batal/' . $m['id_mutasi']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Batalkan mutasi siswa ini?')">Batal</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($mutasi)): ?>
            <tr><td colspan="8" class="text-center">Belum ada data mutasi.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>