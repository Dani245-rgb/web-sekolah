<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="card-welcome">
    <h2>Selamat Datang, <?= esc($guru['nama'] ?? $nama) ?>!</h2>
    <p><strong>Jabatan/Bidang:</strong> <?= esc(ucwords($guru['jabatan'] ?? '-')) ?></p>
    <p><strong>Hari ini:</strong> <?= esc(date('l, d F Y')) ?></p>
</div>

<!-- Widget ringkasan - MASIH DUMMY, belum ada modul Kelas/Jadwal/Nilai/Absensi Guru -->
<div class="grid-4">
    <div class="stat-card stat-blue text-center">
        <i class="bi bi-calendar3 stat-icon"></i>
        <div class="widget-number"><?= esc($ringkasan['kelas_hari_ini']) ?></div>
        <div class="widget-label">Kelas Hari Ini</div>
    </div>
    <div class="stat-card stat-orange text-center">
        <i class="bi bi-pencil-square stat-icon"></i>
        <div class="widget-number"><?= esc($ringkasan['nilai_belum']) ?></div>
        <div class="widget-label">Nilai Belum Diinput</div>
    </div>
    <div class="stat-card stat-green text-center">
        <i class="bi bi-clipboard-check stat-icon"></i>
        <div class="widget-number"><?= esc($ringkasan['absensi_belum']) ?></div>
        <div class="widget-label">Absensi Belum Diisi</div>
    </div>
    <div class="stat-card stat-purple text-center">
        <i class="bi bi-megaphone-fill stat-icon"></i>
        <div class="widget-number"><?= esc($ringkasan['pengumuman_baru']) ?></div>
        <div class="widget-label">Pengumuman Baru</div>
    </div>
</div>

<div class="grid-1">
    <div class="card-widget">
        <h4 style="margin-bottom:16px;">Jadwal Mengajar Hari Ini</h4>
        <?php foreach ($jadwalHariIni as $j): ?>
            <div class="list-item jadwal-item" id="jadwal-hari-ini">
                <div>
                    <strong><?= esc($j['jam']) ?></strong> — <?= esc($j['kelas']) ?> (<?= esc($j['nama_mapel']) ?>)
                </div>
                <div class="jadwal-actions">
                    <a href="<?= base_url('guru/absensi/form/' . $j['id_jadwal']) ?>"
                        class="btn-badge <?= $j['sudah_absen'] ? 'btn-badge-outline' : 'btn-badge-warning' ?>">
                        <i class="bi bi-clipboard-check"></i> <?= $j['sudah_absen'] ? 'Lihat/Edit Absensi' : 'Isi Absensi' ?>
                    </a>
                    <?php if ($j['ada_nilai']): ?>
                        <a href="<?= base_url('guru/nilai/form/' . $j['id_jadwal']) ?>" class="btn-badge btn-badge-primary">
                            <i class="bi bi-pencil-square"></i> Isi Nilai
                        </a>
                    <?php else: ?>
                        <span class="btn-badge btn-badge-disabled" title="Mapel ini tidak memerlukan penilaian">
                            <i class="bi bi-slash-circle"></i> Tanpa Nilai
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($jadwalHariIni)): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <p>Tidak ada jadwal mengajar hari ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="grid-2 mt">
    <div class="card-widget">
        <div class="card-widget-header">
            <h4>Tugas Terbaru</h4>
            <a href="<?= base_url('guru/tugas') ?>" class="link-lihat-semua">Lihat Semua</a>
        </div>
        <?php if (empty($tugasTerbaru)): ?>
            <div class="empty-state-mini">
                <i class="bi bi-folder"></i>
                <span>Belum ada tugas yang dibuat.</span>
            </div>
        <?php else: ?>
            <?php foreach ($tugasTerbaru as $t): ?>
                <a href="<?= base_url('guru/tugas/submisi/' . $t['id_tugas']) ?>" class="quick-list-item">
                    <div>
                        <div class="quick-list-title"><?= esc($t['judul']) ?></div>
                        <div class="quick-list-sub"><?= esc($t['nama_kelas']) ?> · <?= esc($t['nama_mapel']) ?></div>
                    </div>
                    <span class="quick-list-badge"><?= esc($t['jumlah_submisi'] ?? 0) ?> submisi</span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card-widget">
        <div class="card-widget-header">
            <h4>Materi Terbaru</h4>
            <a href="<?= base_url('guru/materi') ?>" class="link-lihat-semua">Lihat Semua</a>
        </div>
        <?php if (empty($materiTerbaru)): ?>
            <div class="empty-state-mini">
                <i class="bi bi-file-earmark"></i>
                <span>Belum ada materi yang diunggah.</span>
            </div>
        <?php else: ?>
            <?php foreach ($materiTerbaru as $m): ?>
                <a href="<?= base_url('assets/uploads/materi/' . $m['file_materi']) ?>" target="_blank" class="quick-list-item">
                    <div>
                        <div class="quick-list-title"><?= esc($m['judul']) ?></div>
                        <div class="quick-list-sub"><?= esc($m['nama_kelas']) ?> · <?= esc($m['nama_mapel']) ?></div>
                    </div>
                    <span class="quick-list-badge"><?= date('d/m', strtotime($m['created_at'])) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="grid-4 mt">
    <a href="<?= base_url('guru/kelas') ?>" class="quick-access-card">
        <i class="bi bi-building"></i>
        <div class="quick-access-label">Data Kelas</div>
        <div class="quick-access-sub"><?= esc($jumlahKelasDiajar) ?> kelas diajar</div>
    </a>
    <a href="<?= base_url('guru/jadwal') ?>" class="quick-access-card">
        <i class="bi bi-calendar3"></i>
        <div class="quick-access-label">Jadwal</div>
        <div class="quick-access-sub">Lihat semua jadwal</div>
    </a>
    <a href="<?= base_url('guru/tugas') ?>" class="quick-access-card">
        <i class="bi bi-folder-fill"></i>
        <div class="quick-access-label">Tugas</div>
        <div class="quick-access-sub"><?= count($tugasTerbaru) ?> terbaru</div>
    </a>
    <a href="<?= base_url('guru/materi') ?>" class="quick-access-card">
        <i class="bi bi-file-earmark-arrow-up-fill"></i>
        <div class="quick-access-label">Materi</div>
        <div class="quick-access-sub"><?= count($materiTerbaru) ?> terbaru</div>
    </a>
</div>

<div class="grid-2 mt">
    <div class="empty-state-mini">
        <i class="bi bi-bar-chart-line"></i>
        <span>Aktivitas Mengajar — tersedia setelah modul Jadwal & Absensi Guru dibuat</span>
    </div>
    <div class="empty-state-mini">
        <i class="bi bi-pie-chart"></i>
        <span>Grafik Absensi Siswa — tersedia setelah modul Absensi dibuat</span>
    </div>
</div>

<?= $this->endSection() ?>