<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<div class="page-header">
    <h4>Data Guru</h4>
    <p class="breadcrumb">Dashboard / Data Guru</p>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
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
        <a href="<?= base_url('admin/guru/create') ?>" class="btn btn-primary">+ Tambah Guru</a>

        <form action="<?= base_url('admin/guru') ?>" method="get" class="search-form">
            <input type="text" name="cari" placeholder="Cari guru..." value="<?= esc($keyword) ?>">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Foto</th>
                <th>NIP</th>
                <th>Nama Guru</th>
                <th>Mata Pelajaran</th>
                <th>No HP</th>
                <th>Email</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = $pager->getCurrentPage() > 1 ? (($pager->getCurrentPage() - 1) * 5) + 1 : 1; ?>
            <?php foreach ($guru as $g): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>
                        <?php if (!empty($g['foto'])): ?>
                            <img src="<?= base_url('uploads/guru/' . $g['foto']) ?>" alt="foto" class="avatar-sm">
                        <?php else: ?>
                            <div class="avatar-initial"><?= esc(strtoupper(substr($g['nama'], 0, 1))) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($g['nip']) ?></td>
                    <td><?= esc($g['nama']) ?></td>
                    <td>-</td> <!-- akan di-join begitu modul Mapel selesai -->
                    <td><?= esc($g['no_hp']) ?></td>
                    <td><?= esc($g['email']) ?></td>
                    <td>
                        <span class="badge <?= $g['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                            <?= esc($g['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= base_url('admin/guru/edit/' . $g['id_guru']) ?>"
                            class="btn btn-sm btn-warning">Edit</a>
                        <form method="post" action="<?= base_url('admin/guru/delete/' . $g['id_guru']) ?>" style="display:inline;"
                            onsubmit="return confirm('Nonaktifkan akun guru ini?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($guru)): ?>
                <tr>
                    <td colspan="9" class="text-center">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i class="bi bi-inbox" style="font-size:24px;color:#d7dce3;"></i>
                            <span>Belum ada data guru.</span>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-wrapper">
        <?= $pager->links('guru', 'default_full') ?>
    </div>
</div>

<?= $this->endSection() ?>