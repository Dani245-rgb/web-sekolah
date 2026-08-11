<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/riwayat-absensi.css') ?>">

<div class="absensi-card">
    <div class="absensi-header">
        <h4>Riwayat Absensi — <?= esc($jadwal['nama_kelas']) ?> / <?= esc($jadwal['nama_mapel']) ?></h4>
    </div>

    <?php if (empty($riwayatList)): ?>
        <p class="absensi-empty">Belum ada riwayat pengisian absensi untuk kelas/mapel ini.</p>
    <?php else: ?>
        <?php foreach ($riwayatList as $r): ?>
            <?php
                // Parse keterangan format: Key:Value|Key:Value|...
                $parts = [];
                foreach (explode('|', $r['keterangan']) as $segment) {
                    [$k, $v] = array_pad(explode(':', $segment, 2), 2, '');
                    $parts[$k] = $v;
                }
                $tidakHadirRaw = $parts['TidakHadir'] ?? '-';
                $daftarTidakHadir = ($tidakHadirRaw === '-' || $tidakHadirRaw === '')
                    ? []
                    : array_map('trim', explode(',', $tidakHadirRaw));
            ?>
            <div class="riwayat-item">
                <div class="riwayat-waktu">
                    Disimpan <?= esc(date('d M Y, H:i', strtotime($r['created_at']))) ?>
                    <?php if (!empty($parts['Tanggal'])): ?>
                        &middot; Tanggal absensi: <strong><?= esc(date('d M Y', strtotime($parts['Tanggal']))) ?></strong>
                    <?php endif; ?>
                </div>

                <div class="riwayat-badges">
                    <span class="badge badge-hadir">Hadir: <?= esc($parts['Hadir'] ?? 0) ?></span>
                    <span class="badge badge-izin">Izin: <?= esc($parts['Izin'] ?? 0) ?></span>
                    <span class="badge badge-sakit">Sakit: <?= esc($parts['Sakit'] ?? 0) ?></span>
                    <span class="badge badge-alfa">Alfa: <?= esc($parts['Alfa'] ?? 0) ?></span>
                </div>

                <div class="riwayat-tidakhadir-label">Siswa Tidak Hadir</div>
                <?php if (empty($daftarTidakHadir)): ?>
                    <div class="riwayat-tidakhadir-kosong">Semua siswa hadir 🎉</div>
                <?php else: ?>
                    <div class="riwayat-tidakhadir-list">
                        <?php foreach ($daftarTidakHadir as $nama): ?>
                            <span class="chip-tidakhadir"><?= esc($nama) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:8px;">
        <a href="<?= base_url('guru/absensi/form/' . $jadwal['id_jadwal']) ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>