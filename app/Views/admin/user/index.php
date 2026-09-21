<?= $this->extend('layouts/admin/main') ?>

<?= $this->section('content') ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h4>User & Akses - <?= esc($roleLabel) ?></h4>
    <?php if ($role === 'admin' && session()->get('is_superadmin')): ?>
        <a href="<?= base_url('admin/user/create-admin') ?>" class="btn btn-primary">+ Tambah Admin</a>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= base_url('admin/user/' . $role) ?>" method="get" class="search-form" style="display:flex;gap:10px;margin-bottom:16px;">
    <input type="text" name="q" value="<?= esc($keyword ?? '') ?>" placeholder="Cari nama, username, atau role..." style="max-width:300px;">
    <button type="submit" class="btn btn-secondary">Cari</button>
    <?php if (!empty($keyword)): ?>
        <a href="<?= base_url('admin/user') ?>" class="btn btn-secondary">Reset</a>
    <?php endif; ?>
</form>

<div class="card">
    <div class="table-responsive-wrap">
<table class="table">
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
                        <span class="badge <?= $u['status'] === 'Aktif' ? 'badge-success' : 'badge-danger' ?>">
                            <?= esc($u['status']) ?>
                        </span>
                    </td>
                    <td><?= (int) $u['login_attempts'] ?></td>
                    <td>
                        <?php if ($u['locked_until'] && strtotime($u['locked_until']) > time()): ?>
                            <span class="badge badge-danger"><?= date('d M Y H:i', strtotime($u['locked_until'])) ?></span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <?php if ((int) $u['id_user'] !== (int) session()->get('id_user')): ?>
                                <form action="<?= base_url('admin/user/reset-password/' . $u['id_user']) ?>" method="post"
                                    onsubmit="return konfirmasiResetPassword(this, '<?= esc($u['username'], 'js') ?>')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-secondary">Reset Password</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#7c8a9c;font-size:13px;">Akun Anda</span>
                            <?php endif; ?>

                            <?php if ($u['locked_until'] && strtotime($u['locked_until']) > time()): ?>
                                <form action="<?= base_url('admin/user/unlock/' . $u['id_user']) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-warning">Buka Kunci</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" style="color:#7c8a9c;">Belum ada akun.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="pagination-wrapper">
    <?= $pager->links($pagerGroup, 'default_full') ?>
</div>

<script>
function konfirmasiResetPassword(form, username) {
    const ketik = prompt(
        `Password akun "${username}" akan direset ke password acak baru, dan akun wajib ganti password saat login berikutnya.\n\nKetik ulang username "${username}" untuk melanjutkan:`
    );

    if (ketik === null) {
        return false;
    }

    if (ketik.trim() !== username) {
        alert('Username yang diketik tidak cocok. Reset password dibatalkan.');
        return false;
    }

    return true;
}
</script>

<?= $this->endSection() ?>