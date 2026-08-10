<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Data Alumni</h4>
    <p class="breadcrumb">Dashboard / Alumni</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card">
    <a href="<?= base_url('admin/alumni/form') ?>" class="btn btn-primary" style="margin-bottom:16px;">+ Luluskan Siswa</a>

    <table class="table">
        <thead>
            <tr>
                <th>No</th><th>NIS</th><th>Nama</th><th>Tahun Ajaran</th><th>Tanggal Lulus</th><th>No. Ijazah</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($alumni as $a): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($a['nis']) ?></td>
                <td><?= esc($a['nama']) ?></td>
                <td><?= esc($a['tahun_ajaran']) ?></td>
                <td><?= esc($a['tanggal_lulus']) ?></td>
                <td><?= esc($a['no_ijazah'] ?? '-') ?></td>
                <td>
                    <a href="<?= base_url('admin/alumni/batal/' . $a['id_alumni']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Batalkan kelulusan siswa ini?')">Batal Luluskan</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($alumni)): ?>
            <tr><td colspan="7" class="text-center">Belum ada data alumni.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>