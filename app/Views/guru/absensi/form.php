<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Isi Absensi — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>
    </div>
    <p class="absensi-tanggal">Tanggal: <strong><?= esc($tanggal) ?></strong></p>

    <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-error">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
        <div><?= esc($error) ?></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form action="<?= base_url('guru/absensi/simpan') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id_jadwal" value="<?= $jadwal['id_jadwal'] ?>">
        <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">

        <table class="absensi-table">
            <thead>
                <tr><th>No</th><th>Nama Siswa</th><th>Status</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($siswaList as $s): ?>
                <?php $statusSaved = $statusTersimpan[$s['id_siswa']]['status'] ?? 'Hadir'; ?>
                <?php $ketSaved = $statusTersimpan[$s['id_siswa']]['keterangan'] ?? ''; ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= esc($s['nama']) ?></td>
                    <td>
                        <select name="status[<?= $s['id_siswa'] ?>]" class="absensi-select" data-status="<?= $statusSaved ?>"
                            onchange="this.setAttribute('data-status', this.value)">
                            <option value="Hadir" <?= $statusSaved === 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                            <option value="Izin" <?= $statusSaved === 'Izin' ? 'selected' : '' ?>>Izin</option>
                            <option value="Sakit" <?= $statusSaved === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                            <option value="Alfa" <?= $statusSaved === 'Alfa' ? 'selected' : '' ?>>Alfa</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="keterangan[<?= $s['id_siswa'] ?>]" value="<?= esc($ketSaved) ?>" placeholder="Opsional" class="absensi-keterangan">
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($siswaList)): ?>
                <tr><td colspan="4" class="absensi-empty">Tidak ada siswa di kelas ini untuk tahun ajaran aktif.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

  <div class="absensi-actions">
            <button type="submit" class="btn btn-primary">Simpan Absensi</button>
            <a href="<?= base_url('guru/absensi/riwayat/' . $jadwal['id_jadwal']) ?>" class="btn btn-secondary">Riwayat</a>
            <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>