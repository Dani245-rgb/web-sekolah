<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/bk-artikel.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="bk-artikel-page-header">
    <h4>Artikel BK</h4>
    <a href="<?= base_url('admin/bk-artikel/create') ?>" class="btn btn-primary">+ Tambah Artikel</a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="bk-artikel-filter-kategori">
    <a href="<?= base_url('admin/bk-artikel') ?>" class="<?= !$kategoriFilter ? 'active' : '' ?>">Semua</a>
    <a href="<?= base_url('admin/bk-artikel?kategori=kesehatan_mental') ?>" class="<?= $kategoriFilter === 'kesehatan_mental' ? 'active' : '' ?>">Kesehatan Mental</a>
    <a href="<?= base_url('admin/bk-artikel?kategori=karier') ?>" class="<?= $kategoriFilter === 'karier' ? 'active' : '' ?>">Karier</a>
    <a href="<?= base_url('admin/bk-artikel?kategori=tes_minat') ?>" class="<?= $kategoriFilter === 'tes_minat' ? 'active' : '' ?>">Tes Minat</a>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Foto</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($artikelList)): ?>
            <?php foreach ($artikelList as $i => $a): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>
                    <?php if (!empty($a['foto'])): ?>
                    <img src="<?= base_url('uploads/bk_artikel/' . esc($a['foto'], 'attr')) ?>"
                         alt="<?= esc($a['judul']) ?>" class="bk-artikel-foto-thumb">
                    <?php else: ?>
                    <span class="bk-artikel-foto-kosong">-</span>
                    <?php endif; ?>
                </td>
                <td><?= esc($a['judul']) ?></td>
                <td>
                    <span class="bk-artikel-badge-kategori bk-artikel-badge-<?= esc($a['kategori'], 'attr') ?>">
                        <?= esc(str_replace('_', ' ', ucfirst($a['kategori']))) ?>
                    </span>
                </td>
                <td>
                    <span class="bk-artikel-badge-status-<?= strtolower($a['status']) ?>">
                        <?= esc($a['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="<?= base_url('admin/bk-artikel/edit/' . $a['id_artikel']) ?>"
                       class="btn btn-sm btn-outline-primary">Edit</a>
                    <a href="<?= base_url('admin/bk-artikel/delete/' . $a['id_artikel']) ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Hapus artikel \'<?= esc($a['judul'], 'js') ?>\'? Tindakan ini tidak bisa dibatalkan.')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">Belum ada artikel.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?= $this->endSection() ?>