<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="page-header">
    <h4>Rekap Absensi — <?= esc($siswa['nama']) ?></h4>
    <p class="breadcrumb">Dashboard / Rekap Absensi / Siswa / Detail</p>
</div>

<div class="card">
    <form action="<?= base_url('admin/rekap-absensi/siswa/detail/' . $siswa['id_siswa']) ?>" method="get" style="display:flex; gap:12px; align-items:end; margin-bottom:20px;">
        <div class="form-group">
            <label>Dari Tanggal</label>
            <input type="date" name="tgl_mulai" value="<?= esc($tglMulai) ?>">
        </div>
        <div class="form-group">
            <label>Sampai Tanggal</label>
            <input type="date" name="tgl_selesai" value="<?= esc($tglSelesai) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <div style="display:flex; gap:16px; margin-bottom:20px;">
        <div class="card-widget" style="flex:1; text-align:center;"><div style="font-size:24px; color:#00b894; font-weight:700;"><?= $rekap['Hadir'] ?></div>Hadir</div>
        <div class="card-widget" style="flex:1; text-align:center;"><div style="font-size:24px; color:#0984e3; font-weight:700;"><?= $rekap['Izin'] ?></div>Izin</div>
        <div class="card-widget" style="flex:1; text-align:center;"><div style="font-size:24px; color:#e17055; font-weight:700;"><?= $rekap['Sakit'] ?></div>Sakit</div>
        <div class="card-widget" style="flex:1; text-align:center;"><div style="font-size:24px; color:#d63031; font-weight:700;"><?= $rekap['Alfa'] ?></div>Alfa</div>
    </div>

    <table class="absensi-table">
        <thead>
            <tr><th>Tanggal</th><th>Mapel</th><th>Status</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            <?php foreach ($detail as $d): ?>
            <tr>
                <td><?= esc($d['tanggal']) ?></td>
                <td><?= esc($d['nama_mapel']) ?></td>
                <td><?= esc($d['status']) ?></td>
                <td><?= esc($d['keterangan'] ?: '-') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($detail)): ?>
            <tr><td colspan="4" class="absensi-empty">Belum ada absensi di rentang tanggal ini.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="<?= base_url('admin/rekap-absensi/siswa') ?>" class="btn btn-secondary" style="margin-top:16px;">Kembali</a>
</div>

<?= $this->endSection() ?>