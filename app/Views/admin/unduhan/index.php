<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/unduhan.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="unduhan-page-header">
    <h4>Pusat Unduhan</h4>
    <a href="<?= base_url('admin/unduhan/create') ?>" class="btn btn-primary">+ Tambah File</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (!empty($kategoriList)): ?>
<div class="unduhan-filter-kategori">
    <a href="<?= base_url('admin/unduhan') ?>" class="<?= !$kategoriFilter ? 'active' : '' ?>">Semua</a>
    <?php foreach ($kategoriList as $k): ?>
    <a href="<?= base_url('admin/unduhan?kategori=' . urlencode($k['kategori'])) ?>"
       class="<?= $kategoriFilter === $k['kategori'] ? 'active' : '' ?>"><?= esc($k['kategori']) ?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="table-responsive-wrap">
<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Tipe</th>
            <th>Ukuran</th>
            <th>Diunduh</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($unduhanList)): ?>
            <?php foreach ($unduhanList as $i => $u): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= esc($u['judul']) ?></td>
                <td><?= esc($u['kategori']) ?></td>
                <td><span class="unduhan-badge-ext"><?= esc(strtoupper($u['ekstensi'])) ?></span></td>
                <td><?= $u['ukuran_file'] ? round($u['ukuran_file'] / 1024, 1) . ' KB' : '-' ?></td>
                <td><?= (int) $u['jumlah_unduh'] ?>x</td>
                <td>
                    <span class="unduhan-badge-status-<?= strtolower($u['status']) ?>">
                        <?= esc($u['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="<?= base_url('admin/unduhan/edit/' . $u['id_unduhan']) ?>"
                       class="btn btn-sm btn-outline-primary">Edit</a>
                    <form method="post" action="<?= base_url('admin/unduhan/delete/' . $u['id_unduhan']) ?>" style="display:inline;"
                       onsubmit="return confirm('Hapus file \'<?= esc($u['judul'], 'js') ?>\'? Tindakan ini tidak bisa dibatalkan.')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">Belum ada file.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<?= $this->endSection() ?>