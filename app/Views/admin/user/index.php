<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/galeri.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>User & Akses - <?= esc($roleLabel) ?></h4>
    <?php if ($role === 'admin' && session()->get('is_superadmin')): ?>
        <a href="<?= base_url('admin/user/create-admin') ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Admin
        </a>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= base_url('admin/user/' . $role) ?>" method="get" class="mb-3 d-flex gap-2">
    <input type="text" name="q" value="<?= esc($keyword ?? '') ?>" class="form-control" placeholder="Cari nama, username, atau role..." style="max-width:300px;">
    <button type="submit" class="btn btn-outline-primary">Cari</button>
    <?php if (!empty($keyword)): ?>
        <a href="<?= base_url('admin/user') ?>" class="btn btn-outline-secondary">Reset</a>
    <?php endif; ?>
</form>

<table class="table-admin">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Percobaan Login</th>
            <th>Terkunci Sampai</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= esc($u['nama_tampil']) ?></td>
                <td><?= esc($u['username']) ?></td>
                <td><?= esc($u['nama_role']) ?></td>
                <td>
                    <span class="badge <?= $u['status'] === 'Aktif' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= esc($u['status']) ?>
                    </span>
                </td>
                <td><?= (int) $u['login_attempts'] ?></td>
                <td>
                    <?php if ($u['locked_until'] && strtotime($u['locked_until']) > time()): ?>
                        <span class="badge bg-danger"><?= date('d M Y H:i', strtotime($u['locked_until'])) ?></span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="d-flex gap-2" style="flex-wrap:wrap;">
                    <?php if ((int) $u['id_user'] !== (int) session()->get('id_user')): ?>
                        <form action="<?= base_url('admin/user/reset-password/' . $u['id_user']) ?>" method="post" onsubmit="return confirm('Reset password akun ini?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Reset Password</button>
                        </form>
                    <?php else: ?>
                        <span class="text-muted">Akun Anda</span>
                    <?php endif; ?>

                    <?php if ($u['locked_until'] && strtotime($u['locked_until']) > time()): ?>
                        <form action="<?= base_url('admin/user/unlock/' . $u['id_user']) ?>" method="post">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Buka Kunci</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($users)): ?>
            <tr>
                <td colspan="7" class="text-muted">Belum ada akun.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="pagination-wrapper" style="margin-top:20px;">
    <?= $pager->links($pagerGroup, 'default_full') ?>
</div>

<?= $this->endSection() ?>