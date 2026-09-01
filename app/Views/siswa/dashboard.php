<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>

<div class="card-welcome">
    <h2>Selamat Datang, <?= esc($siswa['nama'] ?? $nama) ?>!</h2>
    <p><strong>NIS:</strong> <?= esc($siswa['nis'] ?? '-') ?></p>
    <p><strong>Kelas:</strong> <?= esc($siswa['nama_kelas'] ?? '-') ?></p>
</div>

<!-- Widget ringkasan - MASIH DUMMY, belum ada modul Nilai/Absensi/Tugas/Pengumuman -->
<div class="grid-4">
    <div class="stat-card stat-blue text-center">
        <i class="bi bi-pencil-square stat-icon"></i>
        <div class="widget-number"><?= esc($ringkasan['nilai_rata']) ?></div>
        <div class="widget-label">Nilai Rata-rata</div>
    </div>
    <div class="stat-card stat-green text-center">
        <i class="bi bi-clipboard-check stat-icon"></i>
        <div class="widget-number"><?= esc($ringkasan['kehadiran']) ?>%</div>
        <div class="widget-label">Kehadiran Bulan Ini</div>
    </div>
    <div class="stat-card stat-orange text-center">
        <i class="bi bi-folder-fill stat-icon"></i>
        <div class="widget-number"><?= esc($ringkasan['tugas_belum']) ?></div>
        <div class="widget-label">Tugas Belum Selesai</div>
    </div>
    <div class="stat-card stat-purple text-center">
        <i class="bi bi-megaphone-fill stat-icon"></i>
        <div class="widget-number"><?= esc($ringkasan['pengumuman_baru']) ?></div>
        <div class="widget-label">Pengumuman Baru</div>
    </div>
</div>

<div class="grid-2">
    <div class="card-widget">
        <h4 style="margin-bottom:16px;">Jadwal Hari Ini</h4>
        <?php foreach ($jadwalHariIni as $j): ?>
            <a href="<?= base_url('siswa/jadwal/' . ($j['id_jadwal'] ?? '')) ?>" class="list-item list-item-link">
                <strong><?= esc($j['jam']) ?></strong> — <?= esc($j['mapel']) ?>
            </a>
        <?php endforeach; ?>
        <?php if (empty($jadwalHariIni)): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <p>Tidak ada jadwal hari ini.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-widget">
        <h4 style="margin-bottom:16px;">Pengumuman Terbaru</h4>
        <?php foreach ($pengumumanTerbaru as $p): ?>
            <a href="<?= base_url('siswa/pengumuman/' . ($p['id'] ?? '')) ?>" class="list-item list-item-link">
                • <?= esc(is_array($p) ? $p['judul'] : $p) ?>
            </a>
        <?php endforeach; ?>
        <?php if (empty($pengumumanTerbaru)): ?>
            <div class="empty-state">
                <i class="bi bi-megaphone"></i>
                <p>Belum ada pengumuman baru.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="grid-2 mt">
    <div class="empty-state-mini">
        <i class="bi bi-bar-chart-line"></i>
        <span>Grafik Nilai — tersedia setelah modul Nilai dibuat</span>
    </div>
    <div class="empty-state-mini">
        <i class="bi bi-pie-chart"></i>
        <span>Grafik Absensi — tersedia setelah modul Absensi dibuat</span>
    </div>
</div>

<?= $this->endSection() ?>