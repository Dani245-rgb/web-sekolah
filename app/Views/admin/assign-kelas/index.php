<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Assignment Kelas Siswa</h4>
    <p class="breadcrumb">Dashboard / Kelas / Assign Kelas</p>
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
    <a href="<?= base_url('admin/assign-kelas/form') ?>" class="btn btn-primary" style="margin-bottom:16px;">+ Assign Siswa ke Kelas</a>

    <form action="<?= base_url('admin/assign-kelas') ?>" method="get" class="filter-form" style="margin-bottom:16px;">
    <label>Filter Tahun Ajaran:</label>
    <select name="id_tahun_ajaran" onchange="this.form.submit()">
        <?php foreach ($tahunAjaran as $t): ?>
        <option value="<?= $t['id_tahun_ajaran'] ?>" <?= $t['id_tahun_ajaran'] == $idFilter ? 'selected' : '' ?>>
            <?= esc($t['tahun_ajaran']) ?><?= ($tahunAktif && $t['id_tahun_ajaran'] == $tahunAktif['id_tahun_ajaran']) ? ' (Aktif)' : '' ?>
        </option>
        <?php endforeach; ?>
    </select>
</form>

    <table class="table">
        <thead>
            <tr><th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Tahun Ajaran</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($assignments as $a): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($a['nis']) ?></td>
                <td><?= esc($a['nama']) ?></td>
                <td><?= esc($a['nama_kelas']) ?></td>
                <td><?= esc($a['tahun_ajaran']) ?></td>
                <td>
                    <a href="<?= base_url('admin/assign-kelas/batal/' . $a['id_kelas_siswa']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Batalkan assignment kelas siswa ini?')">Batal</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($assignments)): ?>
            <tr><td colspan="6" class="text-center">Belum ada siswa yang di-assign ke kelas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>