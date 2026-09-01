<?= $this->extend('layouts/admin/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h4>Dashboard</h4>
    <p class="breadcrumb">Selamat datang, <?= esc(session()->get('username')) ?></p>
</div>

<?php if (!$tahunAktif): ?>
    <div class="alert alert-error">
        Belum ada Tahun Ajaran yang berstatus Aktif. Sebagian statistik di bawah mungkin tidak akurat sampai kamu mengaktifkan salah satu di menu Tahun Ajaran.
    </div>
<?php endif; ?>

<!-- STAT CARDS -->
<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
    <div class="card" style="flex:1;min-width:180px;display:flex;gap:12px;align-items:center;">
        <div style="width:44px;height:44px;border-radius:10px;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>
            <strong style="color:#7c8a9c;font-size:12px;">Total Siswa Aktif</strong>
            <h3 style="margin-top:2px;font-size:24px;"><?= $totalSiswaAktif ?></h3>
        </div>
    </div>
    <div class="card" style="flex:1;min-width:180px;display:flex;gap:12px;align-items:center;">
        <div style="width:44px;height:44px;border-radius:10px;background:#8b5cf6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div>
            <strong style="color:#7c8a9c;font-size:12px;">Total Guru</strong>
            <h3 style="margin-top:2px;font-size:24px;"><?= $totalGuru ?></h3>
        </div>
    </div>
    <div class="card" style="flex:1;min-width:180px;display:flex;gap:12px;align-items:center;">
        <div style="width:44px;height:44px;border-radius:10px;background:#22c55e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="bi bi-building-fill"></i>
        </div>
        <div>
            <strong style="color:#7c8a9c;font-size:12px;">Total Kelas Aktif</strong>
            <h3 style="margin-top:2px;font-size:24px;"><?= $totalKelas ?></h3>
        </div>
    </div>
    <div class="card" style="flex:1;min-width:180px;display:flex;gap:12px;align-items:center;">
        <div style="width:44px;height:44px;border-radius:10px;background:#f59e0b;color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="bi bi-bar-chart-fill"></i>
        </div>
        <div>
            <strong style="color:#7c8a9c;font-size:12px;">Total Mapel Aktif</strong>
            <h3 style="margin-top:2px;font-size:24px;"><?= $totalMapel ?></h3>
        </div>
    </div>
    <div class="card" style="flex:1;min-width:180px;display:flex;gap:12px;align-items:center;">
        <div style="width:44px;height:44px;border-radius:10px;background:#ec4899;color:#fff;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
            <i class="bi bi-person-plus-fill"></i>
        </div>
        <div>
            <strong style="color:#7c8a9c;font-size:12px;">Siswa Baru <?= esc($tahunAktif['tahun_ajaran'] ?? 'Tahun Ini') ?></strong>
            <h3 style="margin-top:2px;font-size:24px;"><?= $siswaBaruTahunIni ?></h3>
        </div>
    </div>
</div>

<!-- CHARTS -->
<div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:24px;">
    <div class="card" style="flex:2;min-width:320px;">
        <strong>Statistik Siswa Baru per Tahun Ajaran</strong>
        <div style="margin-top:16px;height:260px;">
            <canvas id="chartSiswaBaru"></canvas>
        </div>
    </div>

    <div class="card" style="flex:1;min-width:220px;">
        <strong>Siswa Aktif per Gender</strong>
        <div style="margin-top:16px;height:200px;">
            <canvas id="chartGender"></canvas>
        </div>
        <div style="margin-top:16px;font-size:13px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#3b82f6;margin-right:6px;"></span>Laki-laki</span>
                <strong><?= $perGender['L'] ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#ec4899;margin-right:6px;"></span>Perempuan</span>
                <strong><?= $perGender['P'] ?></strong>
            </div>
        </div>
    </div>

    <div class="card" style="flex:1;min-width:220px;">
        <strong>Siswa per Status</strong>
        <div style="margin-top:16px;height:200px;">
            <canvas id="chartStatus"></canvas>
        </div>
        <div style="margin-top:16px;font-size:13px;">
            <?php $statusColors = ['Aktif' => '#22c55e', 'Lulus' => '#f59e0b', 'Pindah' => '#9ca3af', 'Keluar' => '#ef4444']; ?>
            <?php foreach ($perStatus as $status => $jumlah): ?>
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?= $statusColors[$status] ?? '#3b82f6' ?>;margin-right:6px;"></span><?= esc($status) ?></span>
                    <strong><?= $jumlah ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ROW: Per Jurusan + Absensi + Jadwal + Pengumuman + Aktivitas -->
<div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:24px;align-items:stretch;">

    <!-- Siswa Aktif per Jurusan -->
    <div class="card" style="flex:1;min-width:230px;">
        <strong>Siswa Aktif per Jurusan</strong>
        <div style="margin-top:12px;height:170px;">
            <canvas id="chartJurusan"></canvas>
        </div>
        <div style="margin-top:12px;font-size:12px;">
            <?php
            $jurusanColors = ['#ef4444', '#22c55e', '#8b5cf6', '#f59e0b', '#3b82f6', '#ec4899'];
            $i = 0;
            ?>
            <?php if (empty($perJurusan)): ?>
                <p style="color:#7c8a9c;">Belum ada data.</p>
            <?php else: ?>
                <?php foreach ($perJurusan as $jurusan => $jumlah): ?>
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                        <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:<?= $jurusanColors[$i % count($jurusanColors)] ?>;margin-right:6px;"></span><?= esc($jurusan ?: '-') ?></span>
                        <strong><?= $jumlah ?></strong>
                    </div>
                    <?php $i++; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Absensi Hari Ini -->
    <div class="card" style="flex:1;min-width:230px;">
        <strong>Absensi Hari Ini</strong>
        <?php if ($totalAbsensiHariIni == 0): ?>
            <div class="empty-state-dash" style="margin-top:12px;">
                <i class="bi bi-clipboard-x"></i>
                <span>Belum ada absensi diinput hari ini.</span>
            </div>
        <?php else: ?>
            <div style="position:relative;margin-top:12px;height:170px;display:flex;align-items:center;justify-content:center;">
                <canvas id="chartAbsensi"></canvas>
                <div style="position:absolute;text-align:center;">
                    <div style="font-size:22px;font-weight:700;color:#22c55e;"><?= $persenHadirHariIni ?>%</div>
                    <div style="font-size:11px;color:#7c8a9c;">Kehadiran</div>
                </div>
            </div>
            <?php $absenColors = ['Hadir' => '#22c55e', 'Izin' => '#f59e0b', 'Sakit' => '#3b82f6', 'Alfa' => '#ef4444']; ?>
            <div style="margin-top:12px;font-size:12px;">
                <?php foreach ($rekapAbsensiHariIni as $status => $jumlah): ?>
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                        <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:<?= $absenColors[$status] ?>;margin-right:6px;"></span><?= esc($status) ?></span>
                        <strong><?= $jumlah ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:8px;font-size:11px;color:#b7c0cc;border-top:1px solid #eef1f5;padding-top:8px;">Total Absensi: <?= $totalAbsensiHariIni ?></div>
        <?php endif; ?>
    </div>

    <!-- Jadwal Hari Ini -->
    <div class="card" style="flex:1;min-width:230px;">
        <strong>Jadwal Hari Ini (<?= esc($hariIni) ?>)</strong>
        <div style="margin-top:12px;">
            <?php if (empty($jadwalHariIni)): ?>
                <div class="empty-state-dash">
                    <i class="bi bi-calendar-x"></i>
                    <span>Tidak ada jadwal hari ini.</span>
                </div>
            <?php else: ?>
                <?php foreach ($jadwalHariIni as $j): ?>
                    <div style="padding:8px 0;border-bottom:1px solid #eef1f5;">
                        <div style="font-size:12px;color:#7c8a9c;"><?= esc(substr($j['jam_mulai'], 0, 5)) ?> - <?= esc(substr($j['jam_selesai'], 0, 5)) ?></div>
                        <div style="font-size:13px;font-weight:600;"><?= esc($j['nama_kelas']) ?> - <?= esc($j['nama_mapel']) ?></div>
                        <div style="font-size:12px;color:#7c8a9c;"><?= esc($j['nama_guru']) ?> · <?= esc($j['nama_ruangan']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <a href="<?= base_url('admin/jadwal') ?>" style="font-size:12px;color:#3b82f6;text-decoration:none;display:inline-block;margin-top:auto;padding-top:10px;">Lihat Jadwal Lengkap →</a>
    </div>

    <!-- Pengumuman Terbaru -->
    <div class="card" style="flex:1;min-width:230px;">
        <strong>Pengumuman Terbaru</strong>
        <div style="margin-top:12px;">
            <?php if (empty($pengumumanTerbaru)): ?>
                <div class="empty-state-dash">
                    <i class="bi bi-megaphone"></i>
                    <span>Belum ada pengumuman.</span>
                </div>
            <?php else: ?>
                <?php foreach ($pengumumanTerbaru as $p): ?>
                    <div style="display:flex;gap:8px;padding:8px 0;border-bottom:1px solid #eef1f5;">
                        <div style="width:7px;height:7px;border-radius:50%;background:#3b82f6;margin-top:6px;flex-shrink:0;"></div>
                        <div>
                            <div style="font-size:13px;font-weight:600;"><?= esc($p['judul']) ?></div>
                            <div style="font-size:11px;color:#b7c0cc;margin-top:2px;"><?= date('d M Y', strtotime($p['tanggal_publish'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <a href="<?= base_url('admin/pengumuman') ?>" style="font-size:12px;color:#3b82f6;text-decoration:none;display:inline-block;margin-top:10px;">Lihat Semua Pengumuman →</a>
    </div>

    <!-- Aktivitas Terbaru -->
    <div class="card" style="flex:1;min-width:230px;">
        <strong>Aktivitas Terbaru</strong>
        <div style="margin-top:12px;">
            <?php if (empty($aktivitasTerbaru)): ?>
                <div class="empty-state-dash">
                    <i class="bi bi-clock-history"></i>
                    <span>Belum ada aktivitas.</span>
                </div>
            <?php else: ?>
                <?php foreach ($aktivitasTerbaru as $a): ?>
                    <div style="display:flex;gap:8px;padding:8px 0;border-bottom:1px solid #eef1f5;">
                        <div style="width:7px;height:7px;border-radius:50%;background:#22c55e;margin-top:6px;flex-shrink:0;"></div>
                        <div>
                            <div style="font-size:13px;"><?= esc($a['username'] ?? 'Sistem') ?>: <?= esc($a['keterangan'] ?? $a['aksi']) ?></div>
                            <div style="font-size:11px;color:#b7c0cc;margin-top:2px;"><?= date('d M Y H:i', strtotime($a['created_at'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <a href="<?= base_url('admin/audit-log') ?>" style="font-size:12px;color:#3b82f6;text-decoration:none;display:inline-block;margin-top:10px;">Lihat Semua Aktivitas →</a>
    </div>

</div>

<!-- QUICK ACTIONS -->
<div class="card">
    <strong>Quick Action</strong>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:14px;">
        <a href="<?= base_url('admin/siswa/create') ?>" class="btn btn-accent" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-person-plus"></i> Tambah Siswa</a>
        <a href="<?= base_url('admin/guru/create') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-person-plus"></i> Tambah Guru</a>
        <a href="<?= base_url('admin/kelas') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-building"></i> Tambah Kelas</a>
        <a href="<?= base_url('admin/jadwal') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-calendar-event"></i> Buat Jadwal</a>
        <a href="<?= base_url('admin/pengumuman/create') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-megaphone"></i> Buat Pengumuman</a>
        <a href="<?= base_url('admin/laporan-siswa') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-file-earmark-text"></i> Lihat Laporan</a>
        <a href="<?= base_url('admin/backup-database') ?>" class="btn btn-secondary" style="flex:1;min-width:140px;text-align:center;"><i class="bi bi-hdd-stack"></i> Backup Data</a>
    </div>
</div>

<!-- Notifikasi + Akses Role -->
<div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:24px;">
    <div class="card" style="flex:2;min-width:300px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <strong>Notifikasi Terbaru</strong>
            <a href="<?= base_url('admin/notifikasi') ?>" style="font-size:13px;color:#3b82f6;text-decoration:none;">Lihat Semua →</a>
        </div>
        <?php if (empty($notifikasiTerbaru)): ?>
            <div class="empty-state-dash">
                <i class="bi bi-bell"></i>
                <span>Belum ada notifikasi.</span>
            </div>
        <?php else: ?>
            <?php foreach ($notifikasiTerbaru as $n): ?>
                <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid #eef1f5;">
                    <div style="width:8px;height:8px;border-radius:50%;background:<?= $n['is_read'] ? '#d7dce3' : '#3b82f6' ?>;margin-top:6px;flex-shrink:0;"></div>
                    <div>
                        <div style="font-size:13px;<?= $n['is_read'] ? '' : 'font-weight:600;' ?>"><?= esc($n['judul']) ?></div>
                        <div style="font-size:12px;color:#7c8a9c;margin-top:2px;"><?= esc($n['pesan']) ?></div>
                        <div style="font-size:11px;color:#b7c0cc;margin-top:2px;"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card" style="flex:1;min-width:260px;">
        <strong>Akses Role</strong>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:14px;">
            <div style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #eef1f5;border-radius:8px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#3b82f6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;"><i class="bi bi-shield-check"></i></div>
                <div><strong style="font-size:13px;">Admin</strong>
                    <div style="font-size:12px;color:#7c8a9c;">Akses Penuh</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #eef1f5;border-radius:8px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#22c55e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;"><i class="bi bi-mortarboard"></i></div>
                <div><strong style="font-size:13px;">Guru</strong>
                    <div style="font-size:12px;color:#7c8a9c;">Akses Terbatas</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #eef1f5;border-radius:8px;">
                <div style="width:36px;height:36px;border-radius:8px;background:#ec4899;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;"><i class="bi bi-person-badge"></i></div>
                <div><strong style="font-size:13px;">Siswa</strong>
                    <div style="font-size:12px;color:#7c8a9c;">Akses Terbatas</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    (function() {
        const chartLabels = <?= json_encode($chartLabels) ?>;
        const chartData = <?= json_encode($chartData) ?>;

        if (chartLabels.length > 0) {
            new Chart(document.getElementById('chartSiswaBaru'), {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Siswa Baru',
                        data: chartData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#3b82f6',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        } else {
            document.getElementById('chartSiswaBaru').closest('.card').innerHTML += '<p style="color:#7c8a9c;margin-top:8px;">Belum ada data historis siswa baru.</p>';
        }

        new Chart(document.getElementById('chartGender'), {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [<?= $perGender['L'] ?>, <?= $perGender['P'] ?>],
                    backgroundColor: ['#3b82f6', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '65%'
            }
        });

        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($perStatus)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($perStatus)) ?>,
                    backgroundColor: <?= json_encode(array_map(fn($s) => $statusColors[$s] ?? '#3b82f6', array_keys($perStatus))) ?>,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '65%'
            }
        });

        <?php if (!empty($perJurusan)): ?>
            new Chart(document.getElementById('chartJurusan'), {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_keys($perJurusan)) ?>,
                    datasets: [{
                        data: <?= json_encode(array_values($perJurusan)) ?>,
                        backgroundColor: <?= json_encode(array_slice($jurusanColors, 0, count($perJurusan))) ?>,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '65%'
                }
            });
        <?php endif; ?>

        <?php if ($totalAbsensiHariIni > 0): ?>
            new Chart(document.getElementById('chartAbsensi'), {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_keys($rekapAbsensiHariIni)) ?>,
                    datasets: [{
                        data: <?= json_encode(array_values($rekapAbsensiHariIni)) ?>,
                        backgroundColor: <?= json_encode(array_values($absenColors)) ?>,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    cutout: '75%'
                }
            });
        <?php endif; ?>
    })();
</script>

<?= $this->endSection() ?>