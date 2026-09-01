<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Tahun Ajaran</h4>
    <p class="breadcrumb">Dashboard / Tahun Ajaran</p>
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
        <a href="<?= base_url('admin/tahun-ajaran/create') ?>" class="btn btn-primary">+ Tambah Tahun Ajaran</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tahun Ajaran</th>
                <th>Semester</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($tahunAjaran as $t): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($t['tahun_ajaran']) ?></td>
                    <td><?= esc($t['semester'] ?? '-') ?></td>
                    <td><?= esc($t['tanggal_mulai'] ?? '-') ?></td>
                    <td><?= esc($t['tanggal_selesai'] ?? '-') ?></td>
                    <td>
                        <?php
                        $badge = match ($t['status']) {
                            'Aktif' => 'badge-success',
                            'Tidak Aktif' => 'badge-danger',
                            default => '',
                        };
                        ?>
                        <span class="badge <?= $badge ?>"><?= esc($t['status']) ?></span>
                    </td>
                    <td>
                        <a href="<?= base_url('admin/tahun-ajaran/edit/' . $t['id_tahun_ajaran']) ?>"
                            class="btn btn-sm btn-warning">Edit</a>
                        <form method="post" action="<?= base_url('admin/tahun-ajaran/delete/' . $t['id_tahun_ajaran']) ?>" style="display:inline;"
                            onsubmit="return confirm('Yakin hapus tahun ajaran ini?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($tahunAjaran)): ?>
                <tr>
                    <td colspan="7" class="text-center">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
                            <i class="bi bi-inbox" style="font-size:24px;color:#d7dce3;"></i>
                            <span>Belum ada data tahun ajaran.</span>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>