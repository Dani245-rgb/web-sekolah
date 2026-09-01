<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Data Kelas</h4>
    <p class="breadcrumb">Dashboard / Data Kelas</p>
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
        <a href="<?= base_url('admin/kelas/create') ?>" class="btn btn-primary">+ Tambah Kelas</a>

        <form action="<?= base_url('admin/kelas') ?>" method="get" class="search-form">
            <input type="text" name="cari" placeholder="Cari kelas..." value="<?= esc($keyword) ?>">
            <button type="submit"><i class="icon-search"></i></button>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kelas</th>
                <th>Wali Kelas</th>
                <th>Ruangan</th>
                <th>Jumlah Siswa</th>
                <th>Tahun Ajaran</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = $pager->getCurrentPage() > 1 ? (($pager->getCurrentPage() - 1) * 10) + 1 : 1; ?>
            <?php foreach ($kelas as $k): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><strong><?= esc($k['nama_kelas']) ?></strong></td>
                    <td><?= esc($k['nama_wali'] ?? '-') ?></td>
                    <td><?= esc($k['ruangan'] ?? '-') ?></td>
                    <td>
                        <a href="<?= base_url('admin/kelas/siswa/' . $k['id_kelas']) ?>" class="jumlah-siswa-link">
                            <?= esc($k['jumlah_siswa']) ?> / <?= esc($k['kapasitas']) ?>
                        </a>
                    </td>
                    <td><?= esc($k['tahun_ajaran'] ?? '-') ?></td>
                    <td>
                        <span class="badge <?= $k['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                            <?= esc($k['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= base_url('admin/kelas/edit/' . $k['id_kelas']) ?>"
                            class="btn btn-sm btn-warning">Edit</a>
                        <form method="post" action="<?= base_url('admin/kelas/delete/' . $k['id_kelas']) ?>" style="display:inline;"
                            onsubmit="return confirm('Yakin hapus kelas ini?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($kelas)): ?>
                <tr>
                    <td colspan="8" class="text-center">Belum ada data kelas.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->links('kelas', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>