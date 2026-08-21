<?= $this->extend('layouts/guru/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/absensi.css') ?>">

<div class="card-welcome">
    <h2 style="margin-bottom:12px;">Selamat Datang, <?= esc($guru['nama'] ?? $nama) ?>!</h2>
    <p><strong>Jabatan/Bidang:</strong> <?= esc($guru['jabatan'] ?? '-') ?></p>
    <p><strong>Hari ini:</strong> <?= esc(date('l, d F Y')) ?></p>
</div>

<!-- Widget ringkasan - MASIH DUMMY, belum ada modul Kelas/Jadwal/Nilai/Absensi Guru -->
<div class="grid-4">
    <div class="card-widget text-center">
        <div class="widget-number" style="color:#4a6cf7;"><?= esc($ringkasan['kelas_hari_ini']) ?></div>
        <div class="widget-label">Kelas Hari Ini</div>
    </div>
    <div class="card-widget text-center">
        <div class="widget-number" style="color:#e17055;"><?= esc($ringkasan['nilai_belum']) ?></div>
        <div class="widget-label">Nilai Belum Diinput</div>
    </div>
    <div class="card-widget text-center">
        <div class="widget-number" style="color:#00b894;"><?= esc($ringkasan['absensi_belum']) ?></div>
        <div class="widget-label">Absensi Belum Diisi</div>
    </div>
    <div class="card-widget text-center">
        <div class="widget-number" style="color:#6c5ce7;"><?= esc($ringkasan['pengumuman_baru']) ?></div>
        <div class="widget-label">Pengumuman Baru</div>
    </div>
</div>

<!-- DIUBAH: dari class="grid-2" jadi class="grid-1" karena section ini
     cuma berisi 1 card. Dengan grid-2 (yang selalu 2 kolom), card jadwal
     hanya mengisi kolom pertama dan menyisakan ruang kosong di kanan. -->
<div class="grid-1">
    <div class="card-widget">
        <h4 style="margin-bottom:10px;">Jadwal Mengajar Hari Ini</h4>
     <?php foreach ($jadwalHariIni as $j): ?>
    <div class="list-item" id="jadwal-hari-ini">
        <strong><?= esc($j['jam']) ?></strong> — <?= esc($j['kelas']) ?> (<?= esc($j['nama_mapel']) ?>)
        <a href="<?= base_url('guru/absensi/form/' . $j['id_jadwal']) ?>"
            class="absensi-link-jadwal <?= $j['sudah_absen'] ? '' : 'belum-diisi' ?>"
            style="margin-left:8px;">
            <?= $j['sudah_absen'] ? '(Lihat/Edit Absensi)' : '(Isi Absensi)' ?>
        </a>
        <a href="<?= base_url('guru/nilai/form/' . $j['id_jadwal']) ?>"
            class="absensi-link-jadwal"
            style="margin-left:8px;">
            (Isi Nilai)
        </a>
    </div>
<?php endforeach; ?>
    </div>
</div>

<div class="grid-2 mt">
    <div class="card-widget text-center widget-placeholder">
        Aktivitas Mengajar — tersedia setelah modul Jadwal & Absensi Guru dibuat
    </div>
    <div class="card-widget text-center widget-placeholder">
        Grafik Absensi Siswa — tersedia setelah modul Absensi dibuat
    </div>
</div>

<?= $this->endSection() ?>