<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Jadwal Pelajaran</h4>
    <p class="breadcrumb">Dashboard / Jadwal Pelajaran — Semester: <?= esc($semesterAktif['nama_semester']) ?></p>
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
        <a href="<?= base_url('admin/jadwal/create') ?>" class="btn btn-primary">+ Tambah Jadwal</a>
        <a href="<?= base_url('admin/jadwal/import') ?>" class="btn btn-secondary">📊 Import Excel</a>
        <a href="<?= base_url('admin/jadwal/template') ?>" class="btn btn-secondary">⬇️ Template</a>
        <a href="<?= base_url('admin/jadwal/export') ?>" class="btn btn-secondary">📤 Export Excel</a>
        <?php $qs = http_build_query(array_filter($filter));
        $qs = $qs ? '?' . $qs : ''; ?>
        <a href="<?= base_url('admin/jadwal/export/pdf' . $qs) ?>" class="btn btn-outline-danger">📄 Export PDF</a>
    </div>

    <form method="get" class="filter-bar">
        <select name="id_jurusan" onchange="this.form.submit()">
            <option value="">-- Semua Jurusan --</option>
            <?php foreach ($jurusan as $j): ?>
                <option value="<?= $j['id_jurusan'] ?>"
                    <?= ($filter['id_jurusan'] ?? '') == $j['id_jurusan'] ? 'selected' : '' ?>>
                    <?= esc($j['nama_jurusan']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="id_kelas" onchange="this.form.submit()">
            <option value="">-- Semua Kelas --</option>
            <?php foreach ($kelas as $k): ?>
                <option value="<?= $k['id_kelas'] ?>"
                    <?= ($filter['id_kelas'] ?? '') == $k['id_kelas'] ? 'selected' : '' ?>>
                    <?= esc($k['nama_kelas']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="hari" onchange="this.form.submit()">
            <option value="">-- Semua Hari --</option>
            <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h): ?>
                <option value="<?= $h ?>" <?= ($filter['hari'] ?? '') == $h ? 'selected' : '' ?>><?= $h ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="keyword" placeholder="Cari mapel/guru/kelas..."
            value="<?= esc($filter['keyword'] ?? '') ?>">
        <button type="submit" class="btn btn-secondary">Cari</button>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Hari</th>
                <th>Jam</th>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>Mapel</th>
                <th>Guru</th>
                <th>Ruangan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($jadwal as $j): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($j['hari']) ?></td>
                    <td><?= esc(substr($j['jam_mulai'], 0, 5)) ?> - <?= esc(substr($j['jam_selesai'], 0, 5)) ?></td>
                    <td><?= esc($j['nama_kelas']) ?></td>
                    <td><?= esc($j['nama_jurusan'] ?? '-') ?></td>
                    <td><?= esc($j['nama_mapel']) ?></td>
                    <td><?= esc($j['nama_guru']) ?></td>
                    <td><?= esc($j['nama_ruangan']) ?></td>
                    <td><?= esc($j['status']) ?></td>
                    <td>
                        <a href="<?= base_url('admin/jadwal/edit/' . $j['id_jadwal']) ?>"
                            class="btn btn-sm btn-warning">Edit</a>
                        <a href="<?= base_url('admin/jadwal/delete/' . $j['id_jadwal']) ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('Yakin hapus?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($jadwal)): ?>
                <tr>
                    <td colspan="10" class="text-center">Belum ada jadwal.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>