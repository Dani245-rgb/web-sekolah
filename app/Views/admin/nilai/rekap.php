<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Rekap Nilai</h4>
    <p class="breadcrumb">Dashboard / Rekap Nilai — Semester: <?= esc($semesterAktif['nama_semester']) ?></p>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-error">
    <?php foreach (session()->getFlashdata('errors') as $error): ?>
        <div><?= esc($error) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <form method="get" class="filter-bar">
        <select name="id_kelas" onchange="this.form.submit()">
            <option value="">-- Semua Kelas --</option>
            <?php foreach ($kelasList as $k): ?>
                <option value="<?= $k['id_kelas'] ?>" <?= ($filter['id_kelas'] ?? '') == $k['id_kelas'] ? 'selected' : '' ?>>
                    <?= esc($k['nama_kelas']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="id_mapel" onchange="this.form.submit()">
            <option value="">-- Semua Mapel --</option>
            <?php foreach ($mapelList as $m): ?>
                <option value="<?= $m['id_mapel'] ?>" <?= ($filter['id_mapel'] ?? '') == $m['id_mapel'] ? 'selected' : '' ?>>
                    <?= esc($m['nama_mapel']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="id_guru" onchange="this.form.submit()">
            <option value="">-- Semua Guru --</option>
            <?php foreach ($guruList as $g): ?>
                <option value="<?= $g['id_guru'] ?>" <?= ($filter['id_guru'] ?? '') == $g['id_guru'] ? 'selected' : '' ?>>
                    <?= esc($g['nama']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <a href="<?= base_url('admin/nilai/rekap') ?>" class="btn btn-secondary">Reset</a>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>No</th><th>Kelas</th><th>Mapel</th><th>Guru</th><th>KKM</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($daftarPengaturan as $p): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= esc($p['nama_kelas']) ?></td>
                <td><?= esc($p['nama_mapel']) ?></td>
                <td><?= esc($p['nama_guru']) ?></td>
                <td><?= esc($p['kkm']) ?></td>
                <td>
                    <a href="<?= base_url('admin/nilai/rekap/' . $p['id_pengaturan']) ?>" class="btn btn-sm btn-primary">Lihat Detail</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($daftarPengaturan)): ?>
            <tr><td colspan="6" class="text-center">Belum ada pengaturan nilai untuk semester ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>