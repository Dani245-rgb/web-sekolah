<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/jurusan.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="jurusan-page-header">
    <h4>Data Jurusan</h4>
    <a href="<?= base_url('admin/jurusan/create') ?>" class="btn btn-primary">+ Tambah Jurusan</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="table-responsive-wrap">
<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Foto</th>
            <th>Nama Jurusan</th>
            <th>Slug</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($jurusanList)): ?>
            <?php foreach ($jurusanList as $i => $j): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>
                    <?php if (!empty($j['foto'])): ?>
                    <img src="<?= base_url('uploads/jurusan/' . esc($j['foto'], 'attr')) ?>"
                         alt="<?= esc($j['nama_jurusan']) ?>" class="jurusan-foto-thumb">
                    <?php else: ?>
                    <span class="jurusan-foto-kosong">-</span>
                    <?php endif; ?>
                </td>
                <td><?= esc($j['nama_jurusan']) ?></td>
                <td><?= esc($j['slug']) ?></td>
                <td>
                    <div class="aksi-actions">
                        <a href="<?= base_url('admin/jurusan/edit/' . $j['id_jurusan']) ?>"
                           class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="post" action="<?= base_url('admin/jurusan/delete/' . $j['id_jurusan']) ?>"
                           onsubmit="return confirm('Hapus jurusan \'<?= esc($j['nama_jurusan'], 'js') ?>\'? Tindakan ini tidak bisa dibatalkan.')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">Belum ada data jurusan.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?= $this->endSection() ?>