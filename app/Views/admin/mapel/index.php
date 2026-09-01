<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Mata Pelajaran</h4>
    <p class="breadcrumb">Dashboard / Mata Pelajaran</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
    <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-toolbar">
        <a href="<?= base_url('admin/mapel/create') ?>" class="btn btn-primary">+ Tambah Mapel</a>

        <form action="<?= base_url('admin/mapel') ?>" method="get" class="search-form">
            <input type="text" name="cari" placeholder="Cari mapel..." value="<?= esc($keyword) ?>">
            <button type="submit"><i class="icon-search"></i></button>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Mapel</th>
                <th>Kelompok</th>
                <th>KKM</th>
                <th>Semester</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = $pager->getCurrentPage() > 1 ? (($pager->getCurrentPage() - 1) * 10) + 1 : 1; ?>
            <?php foreach ($mapel as $m): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($m['kode_mapel']) ?></td>
                <td><strong><?= esc($m['nama_mapel']) ?></strong></td>
                <td><?= esc($m['kelompok_mapel'] ?? '-') ?></td>
                <td><?= esc($m['kkm']) ?></td>
                <td><?= esc($m['semester'] ?? '-') ?></td>
                <td>
                    <span class="badge <?= $m['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                        <?= esc($m['status']) ?>
                    </span>
                </td>
                <td>
                    <a href="<?= base_url('admin/mapel/edit/' . $m['id_mapel']) ?>"
                        class="btn btn-sm btn-warning">Edit</a>
                    <form method="post" action="<?= base_url('admin/mapel/delete/' . $m['id_mapel']) ?>" style="display:inline;"
                        onsubmit="return confirm('Yakin hapus mapel ini?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($mapel)): ?>
            <tr>
                <td colspan="8" class="text-center">Belum ada data mapel.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->links('mapel', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>