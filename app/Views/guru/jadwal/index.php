<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard_guru/jadwal.css') ?>">

<div class="absensi-card jadwal-wrap">
    <div class="absensi-header">
        <h4>Jadwal Mengajar</h4>
    </div>

    <div class="jadwal-summary">
        <span class="jadwal-summary-number"><?= $totalJadwal ?></span>
        <span class="jadwal-summary-label">jadwal mengajar per minggu</span>
    </div>

    <?php if ($totalJadwal === 0): ?>
        <div class="jadwal-empty">
            Belum ada jadwal mengajar yang di-assign untuk Anda. Silakan hubungi Admin.
        </div>
    <?php else: ?>

        <div class="jadwal-bento">
            <?php foreach ($jadwalPerHari as $hari => $daftar): ?>
                <?php if (empty($daftar)) continue; ?>
                <div class="jadwal-day-card">
                    <div class="jadwal-day-title"><?= esc($hari) ?></div>
                    <div class="jadwal-day-count"><?= count($daftar) ?> jadwal</div>

                    <?php foreach ($daftar as $j): ?>
                        <div class="jadwal-row">
                            <div class="jadwal-time"><?= substr($j['jam_mulai'], 0, 5) ?>–<?= substr($j['jam_selesai'], 0, 5) ?></div>

                            <div class="jadwal-info">
                                <div class="jadwal-mapel"><?= esc($j['nama_mapel']) ?></div>
                                <div class="jadwal-kelas"><?= esc($j['nama_kelas']) ?></div>
                            </div>

                            <div class="jadwal-actions">
                                <a href="<?= base_url('guru/absensi/form/' . $j['id_jadwal']) ?>" class="jadwal-action-btn jadwal-action-primary" data-label="Absensi">
                                    <i class="bi bi-clipboard-check"></i>
                                </a>
                                <a href="<?= base_url('guru/nilai/form/' . $j['id_jadwal']) ?>" class="jadwal-action-btn jadwal-action-secondary" data-label="Nilai">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?= base_url('guru/tugas/create/' . $j['id_jadwal']) ?>" class="jadwal-action-btn jadwal-action-secondary" data-label="Tugas">
                                    <i class="bi bi-folder-fill"></i>
                                </a>
                                <a href="<?= base_url('guru/materi/create/' . $j['id_jadwal']) ?>" class="jadwal-action-btn jadwal-action-secondary" data-label="Materi">
                                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

    <div class="absensi-actions" style="margin-top:24px;">
        <a href="<?= base_url('guru/dashboard') ?>" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<?= $this->endSection() ?>