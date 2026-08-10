<?= $this->extend('layouts/siswa/main') ?>
<?= $this->section('content') ?>

<div class="card-welcome">
    <h2 style="margin-bottom:12px;">Selamat Datang, <?= esc($siswa['nama'] ?? $nama) ?>!</h2>
    <p><strong>NIS:</strong> <?= esc($siswa['nis'] ?? '-') ?></p>
    <p><strong>Kelas:</strong> <?= esc($siswa['nama_kelas'] ?? '-') ?></p>
</div>

<!-- Widget ringkasan - MASIH DUMMY, belum ada modul Nilai/Absensi/Tugas/Pengumuman -->
<div class="grid-4">
    <div class="card-widget text-center">
        <div class="widget-number" style="color:#4a6cf7;"><?= esc($ringkasan['nilai_rata']) ?></div>
        <div class="widget-label">Nilai Rata-rata</div>
    </div>
    <div class="card-widget text-center">
        <div class="widget-number" style="color:#00b894;"><?= esc($ringkasan['kehadiran']) ?>%</div>
        <div class="widget-label">Kehadiran Bulan Ini</div>
    </div>
    <div class="card-widget text-center">
        <div class="widget-number" style="color:#e17055;"><?= esc($ringkasan['tugas_belum']) ?></div>
        <div class="widget-label">Tugas Belum Selesai</div>
    </div>
    <div class="card-widget text-center">
        <div class="widget-number" style="color:#6c5ce7;"><?= esc($ringkasan['pengumuman_baru']) ?></div>
        <div class="widget-label">Pengumuman Baru</div>
    </div>
</div>

<div class="grid-2">
    <div class="card-widget">
        <h4 style="margin-bottom:10px;">Jadwal Hari Ini</h4>
        <?php foreach ($jadwalHariIni as $j): ?>
        <div class="list-item"><strong><?= esc($j['jam']) ?></strong> — <?= esc($j['mapel']) ?></div>
        <?php endforeach; ?>
    </div>

    <div class="card-widget">
        <h4 style="margin-bottom:10px;">Pengumuman Terbaru</h4>
        <?php foreach ($pengumumanTerbaru as $p): ?>
        <div class="list-item">• <?= esc($p) ?></div>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid-2 mt">
    <div class="card-widget text-center widget-placeholder">
        Grafik Nilai — tersedia setelah modul Nilai dibuat
    </div>
    <div class="card-widget text-center widget-placeholder">
        Grafik Absensi — tersedia setelah modul Absensi dibuat
    </div>
</div>

<?= $this->endSection() ?>