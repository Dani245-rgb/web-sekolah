<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="page-header">
    <h4>Rekap Absensi — Per Kelas</h4>
    <p class="breadcrumb">Dashboard / Rekap Absensi</p>
</div>

<div class="rekap-toolbar">
    <a href="<?= base_url('admin/rekap-absensi/siswa') ?>" class="rekap-toolbar-link">Lihat Rekap per Siswa &rarr;</a>
   <?php if (!empty($idKelas)): ?>
        <a href="<?= base_url('admin/rekap-absensi/export/pdf?id_kelas=' . $idKelas . '&tanggal=' . $tanggal) ?>"
            class="btn btn-outline-danger" style="margin-left:12px;">📄 Export PDF</a>
        <a href="<?= base_url('admin/rekap-absensi/export/excel?id_kelas=' . $idKelas . '&tanggal=' . $tanggal) ?>"
            class="btn btn-outline-success">📊 Export Excel</a>
    <?php endif; ?>
</div>

<div class="card">
    <form action="<?= base_url('admin/rekap-absensi') ?>" method="get" class="absensi-filter-bar">
        <div class="absensi-filter-group">
            <label>Kelas</label>
            <select name="id_kelas" class="absensi-filter-select" required>
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas as $k): ?>
                    <option value="<?= $k['id_kelas'] ?>" <?= $k['id_kelas'] == $idKelas ? 'selected' : '' ?>>
                        <?= esc($k['nama_kelas']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="absensi-filter-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" value="<?= esc($tanggal) ?>" class="absensi-filter-date">
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <table class="absensi-table">
        <thead>
            <tr>
                <th>Jam</th>
                <th>Mapel</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hasil as $h): ?>
                <tr>
                    <td><?= esc(substr($h['jam_mulai'], 0, 5)) ?>-<?= esc(substr($h['jam_selesai'], 0, 5)) ?></td>
                    <td><?= esc($h['nama_mapel']) ?></td>
                    <td><?= esc($h['nis']) ?></td>
                    <td><?= esc($h['nama']) ?></td>
                    <td><span class="absensi-select" data-status="<?= $h['status'] ?>" style="border:none; padding:2px 8px;"><?= esc($h['status']) ?></span></td>
                    <td><?= esc($h['keterangan'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($hasil)): ?>
                <tr>
                    <td colspan="6" class="absensi-empty">
                        <?= $idKelas ? 'Belum ada absensi untuk kelas & tanggal ini.' : 'Pilih kelas dan tanggal dulu.' ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>